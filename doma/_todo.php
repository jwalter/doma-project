<?php
  /*

  **** use cases:
  * som användare vill jag redigera min användarprofil
  * som användare vill jag ladda upp nya kartor
  * som användare vill jag redigera kartor
  * som användare vill jag ta bort kartor så att de även försvinner från disk
  * som besökare vill jag välja år att se på kartor från
  * som besökare vill jag se ett rss-flöde med senaste kartorna
  * som besökare vill jag lista alla användare
  * som besökare vill titta på kartor en och en
  * som besökare vill jag navigera mellan kartor i visaläget
  * som admin vill jag lägga till en användare
  * som admin vill jag redigera en användare
  * som admin vill jag ta bort en användare och dess kartor
  * som admin vill jag ändra språk
  * som admin vill jag ändra databasuppgifter
  * som admin vill jag få vettiga felmeddelanden när nåt går snett
  * som quickroute 2.2-användare vill jag använda webbservicen

  * doma-webbsida
    - beskrivning
    - roller
    - steg för steg för rollerna
    - nya skärmdumpar

  * Strukturera upp css-fil

  * Strukturera upp språkfil

  * svensk språkfil

  * Bättre välkomstmeddelande
    - Skapande av hela siten
    - Skapande av ny användare
    - välkomstsida när användare skapat nytt arkiv

  * chmod måste göras manuellt på vissa servrar

  
  DOMA 3
  
  * google maps för en karta / för flera kartor
  * webbservice för trackback
  * idiotsäkra QuickRoute Jpeg Extension, både i PHP och i .NET
  * skapa klass som kapslar in QuickRoute Extension Data, med relevanta funktioner och cachning, både i php och .net
  * KML/KMZ och återuppspelning: http://code.google.com/intl/sv-SE/apis/kml/documentation/time.html
  * javascriptåteruppspelning
  * skapa KMZ-fil med en/flera kartor
  * logik i Map-klassen för att räkna ut aktuell puls, fart m m givet tid (skippa ParameterizedLocation)
  * autoinpassning baserad på qrt-fil/geokodad jpg-fil fungerar ej klockrent, undersök varför
  * Beräkning av straight line distance för hela banan
  * Både skrivning och läsning av QuickRoute Jpeg Extension Data i PHP, går det mtp 32-bitarsbegränsningen?
  * Byt ordning på användare och senaste kartor på users.php. Lägg inställningen för antalet senaste kartor i config.php.
  * Sökning på kartor
  * lösenordsskydda kartor
  * tävlingar som egna objekt
  * blanka kartor (med tidsinställning för visning - hur spara dem på disk?)
  
  QuickRoute 3
  * Kolla så att zoomnivån vid export inte påverkar data i QR Jpeg Extension, i annat fall inkludera zoomnivå
  * Se över hela grafikmotorn
  * Bortfiltrering av ointressanta punkter vid utritning (men ej i data)
  * Björtes punkter
  * Värden i kartan (som OCAD:s kontrollnummer)
  * Jan Kocbach 3: bomberäkning
  
  
  
  
  
  */
?>