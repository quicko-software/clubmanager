<?php

namespace Quicko\Clubmanager\Domain\Repository;


use TYPO3\CMS\Extbase\Persistence\Repository;

use Quicko\Clubmanager\Domain\Model\Category;
use Quicko\Clubmanager\Domain\Repository\PersistAndRefetchTrait;
use Quicko\Clubmanager\Utils\SlugUtil;

class CategoryRepository extends Repository
{
  use PersistAndRefetchTrait;

  //
  // Call '/Mitglieder-Kategorien/Qualifikationen/Azubi' and 
  // you get the category 'Azubi',
  // which has the parent 'Qualifikationen',
  // which has the parent 'Mitglieder-Kategorien'
  // which has no parent.
  // The tree of categories is created, if it not yet exist.
  //
  public function getOrCreateByNamePath($namePath)
  {
    $deepestCategory = null;
    $names = explode('/', $namePath);
    $parentUid = 0;
    foreach ($names as $name) {
      if ($name === '') {
        continue;
      }
      $deepestCategory = $this->getOrCreateCategory($name, $parentUid);
      $parentUid = $deepestCategory->getUid();
    }
    return $deepestCategory;
  }

  private function getOrCreateCategory($name, $parentUid)
  {
    $query = $this->createQuery();
    $object = $query->matching(
      $query->logicalAnd([
        $query->equals('title', $name),
        $query->equals('parent', $parentUid)
      ])
    )->execute()->getFirst();

    if ($object === null) {
      $object = new Category();
      $object->setTitle($name);
     
      $parent = $this->findByUid($parentUid);
      if ($parent) {
        $object->setParent($parent);
      }
      $object->setSlug(SlugUtil::generateSlug(["title" => $name],0,'sys_category'));
     
      $object = $this->persistAndRefetch($object);
    }

    return $object;
  }


  public function findByParents(array $parentUids)
  {
    $query = $this->createQuery();
    return $query->matching(
      $query->logicalAnd(
        $query->in('parent', $parentUids),
      )
    )->execute();
  }

  public function getFlatChildrenList(?array $parentUids): array
  {
    $children = $this->findByParents($parentUids);
    $result = [];
    if ($children->count() > 0) {
      foreach ($children as $child) {
        $subChildren = $this->getFlatChildrenList([$child->getUid()]);

        if ($subChildren) {
          $result = array_merge($result, $subChildren);
        }
        $result[] = $child;
      }
    }
    return $result;
  }

  public function buildTree($elements, $parentId = null)
  {
    $tree = [];

    foreach ($elements as $element) {
      $parent = $element->getParent();
      if (($parentId == null && $parent == null) || ($parent != null && $parent->getUid() == $parentId)) {
        $children = $this->buildTree($elements, $element->getUid());
        $tree[] = [
          "name" => $element->getTitle(),
          "value" => $element->getUid(),
          "children" => $children
        ];
      }
    }

    return $tree;
  }

  public function buildTreeWithParent($elements, int $parentId)
  {
    $tree = [];
    foreach ($elements as $element) {
      if ($element->getUid() == $parentId) {
        $children = $this->buildTree($elements, $element->getUid());
        $tree[] = [
          "name" => $element->getTitle(),
          "value" => $element->getUid(),
          "children" => $children
        ];
      }
    }

    return $tree;
  }
}
