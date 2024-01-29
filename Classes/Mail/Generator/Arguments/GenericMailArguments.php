<?php

namespace Quicko\Clubmanager\Mail\Generator\Arguments;

class GenericMailArguments extends MemberUidArguments
{
    /**
     * Label.
     */
    public string $label;

    /**
     * MailTo.
     */
    public string $mailTo;

    /**
     * MailToName.
     */
    public string $mailToName;

    /**
     * subject.
     */
    public string $subject;

    /**
     * configRefPid.
     *
     * @var ?int
     */
    public ?int $configRefPid;

    /**
     * TemplateName.
     */
    public string $templateName;

    /**
     * FluidVars.
     *
     * @var ?array
     */
    public array $fluidVars;

    /**
     * Attachments.
     *
     * @var ?array
     */
    public array $attachments;

    /**
     * deleteAttachmentsAfterSend.
     */
    public bool $deleteAttachmentsAfterSend = false;
}
