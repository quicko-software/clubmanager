<?php

namespace Quicko\Clubmanager\DummyData;


class DummyValues {

  const MALE_FIRSTNAMES = 
    'Alfred,Bernd,Caspar,Dieter,Emil,
    Ferdinand,Gerhard,Helmut,Idefix,Jürgen,
    Klaus,Leonard,Manfred,Norbert,Olaf,
    Paul,Quasimodo,Ronald,Siegmund,Toralf,
    Uwe,Viktor,Wilhelm,Xavier,Yul,Zonentoni
  ';
  const FEMALE_FIRSTNAMES =
    'Anne,Bernadette,Corinna,Delia,Estonia,
    Fiete,Gudrun,Helga,Ingrid,Johanna,
    Katharina,Liesbeth,Maria,Natalia,Olga,
    Petra,Quintessentia,Renate,Sieglinde,Tasmania,
    Ursula,Valentina,Wiebke,Xylophonia,Yvonne,Zonkette
  ';
  const LASTNAMES = 
    'Ackermann,Auchnich,Allerson,Alberich,Ammerich,
    Becker,Bender,Ballhaus,Bastmeyr,Bokelbach,
    Circus,Claus,Caspari,Carret,Cobermann,
    Dietrich,Dossel,Dammann,Dreihaupt,Dalmayr,
    Emmerich,Einstein,Eber,Enzelsberg,Eckhart,
    Faust,Feuerbach,Finkelstein,Funkel,Förster,
    Gazetto,Gehringer,Giesbach,Gobel,Grunewald,
    Hanke,Heringer,Hiebfest,Helmholtz,Hübl,
    Imke,Iehlefeld,Itzehog,Irsiegler,Igelei,
    Janauske,Jeberschmidt,Juckelback,Jaus,Jahn,
    Karenzmann,Klabuschke,Kaltmacher,Klobschke,Krampowski,
    Lehmann,Lauterbach,Latauschke,Lieske,Ludwig,
    Meier,Maltowski,Mubretta,Mengkenke,Moltke,
    Niemayr,Nauske,Nubrow,Nehleberg,Niedering,
    Olschewski,Okawanga,Olm,Ohara,Ohrmann,
    Plattner,Peine,Petermann,Pinky,Platuschewsky,
    Querulante,Quermann,Quatararo,Quebecker,Queis,
    Radolfzeller,Rasthaus,Rohrbach,Reis,Rammel,
    Schmidt,Schönemann,Spoiler,Sahne,Saulus-Paulus,
    Tormann,Tabularasa,Tascheleer,Tarrot,Teller,
    Uhlmann,Ulk,Uterus,Urmelei,Ulmenhain,
    Weinhard,Wege,Wilhelm,Wartburg,Waage,
    Xangsverein,Xkalibur,Xtraviel,Xantippe,Xaus,
    Yakel,Yuselfreund,Yaroslaw,Yeti,Yohann,
    Zauber,Zeppelin,Zwedorn,Zwickel,Zeiler
  ';

  const CITYNAMES = '
    Allersberg,Allershausen,Aachen,Albeck,Ammergau,
    Bonn,Berlin,Bottrop,Balingen,Bochum,
    Caselwitz,Clausberg,Creunitz,Cursdorf,Cumbach,
    Düsseldorf,Dortmund,Dinslaken,Delitzsch,Dingelstädt,
    Essen,Euskirchen,Eichenberg,Eisleben,Eisenach,
    Friedrichsbrunn,Freist,Feudelstadt,Fasanerie,Fleckenzechlin,
    Gera,Griesbach,Gauting,Gerolstein,Güntersdorf,
    Hagen,Henningsdorf,Halle (Saale),Hinkelstein,Hainchen,
    Ihlewitz,Ilmenau,Isarlohn,Isserstedt,Ichstedt,
    Jena,Jade,Jameln,Jesteburg,Jever,
    Kalbe,Kirchdorf,Königslutter am Elm,Klein Meckelsen,Kaiserslautern,
    Lehbach,Losheim am See,Lichtenfels,Lauf,Leipzig,
    Merzig,Marburg,München,Meschede,Meuselwitz,
    Naumburg,Nürnberg,Naunhof,Nossen,Nehlen,Niemberg,
    Osnabrück,Osendorf,Orlamünde,Otting,Offenburg,
    Pasewalk,Potsdam,Paulshorst,Prenzlau,Plauen,
    Quedlinburg,Querfurt,Quarnbeck,Quickborn,Quenstedt,
    Rosenheim,Radolfzell,Rieth,Rammelburg,Rambach,
    Staßfurt,Stuttgart,Schönebeck,Saurasen,Sonthofen,
    Thalheim,Turmberg,Trotha,Trebitz,Treptow,
    Uerdingen,Uelzen,Uettingen,Ufering,Uhlenhorst,
    Velbert,Vatterode,Velburg,Vettweiß,Vieritz,
    Weilheim,Wasserburg,Wittenberg,Wiedemar,Weißenstadt,
    Xanten,Xenstedt,Xter,Xarborg,Xahlaus,
    Yach (Elzach),Yasmünde,Yanker,Yoachimsthal,Yockelberg,
    Zerbst,Zabitz,Zellewitz,Zaschwitz,Zscherben
  ';

  const ACADEMIC_TITLES = '
    Dr.,Prof. Dr.,Dr. habil.
  ';

  const CATEGORIES = [
    // create those first, that are used in configurations as parents to have a certain uid here
    '/Veranstaltungstypen', // uid => 1
    '/Standorttypen', // uid => 2
    '/Mitglieder-Kategorien', // uid => 3
    '/Mitglieder-Kategorien/Qualifikationen', // uid => 4
    '/Mitglieder-Kategorien/Rollen', // uid => 5

    '/Mitglieder-Kategorien/Qualifikationen/Azubi',
    '/Mitglieder-Kategorien/Qualifikationen/Doktor',
    '/Mitglieder-Kategorien/Qualifikationen/Geselle',
    '/Mitglieder-Kategorien/Qualifikationen/Ingenieur',
    '/Mitglieder-Kategorien/Qualifikationen/Meister',
    '/Mitglieder-Kategorien/Qualifikationen/Schüler',
    '/Mitglieder-Kategorien/Qualifikationen/Student',

    '/Mitglieder-Kategorien/Rollen/Assistent',
    '/Mitglieder-Kategorien/Rollen/Berater',
    '/Mitglieder-Kategorien/Rollen/Referent',

    '/Veranstaltungstypen/Mitgliederversammlung',
    '/Veranstaltungstypen/Feier',
    '/Veranstaltungstypen/Besprechung',
    '/Veranstaltungstypen/Wanderung',
    '/Veranstaltungstypen/Incentive',
    '/Veranstaltungstypen/Schulung',
    '/Veranstaltungstypen/Sport',
    
    '/Standorttypen/Büro',
    '/Standorttypen/Halle',
    '/Standorttypen/Praxis',
    '/Standorttypen/Sportstätte',
    '/Standorttypen/Werkstatt',
    '/Standorttypen/Gedenkstätte',
  ];
}
