Zu beachtende Templates
======

Frontend-Users
-----

- [ ] Login Window (qui-window-popup box)
- [ ] Profile
    - [ ] Benutzerdaten
        - [ ] Meine Daten
        - [ ] Passwort ändern
        - [ ] Benutzerbild
        - [ ] Benutzerkonto löschen
    - [ ] Shop
        - [ ] Meine Bestellungen
        - [ ] Meine offene Bestellungen


Products
------

- [ ] Produkt Kategorie
- [ ] Einzelnes Produkt


Orders
------

- [ ] Basket small
- [ ] Basket (Im Bestellprozess zu finden)
- [ ] Order Process


Bricks
-------

#### Startseite

1. **Produkte: Promoslider** (\QUI\ProductBricks\Controls\Slider\ProductSlider)
  - Unterhalb des Kopfbereiches (Header Suffix)
  - Zeit zum nächsten Slide (Verzögerung): 7000
  - Hintergrundfarbe: #f5f5f5
  - Hintergrundbild: Ein Beispielbild befindet sich im Ordner bin/image/bricks/demo/Slider
  - Produkte: Paar Produkte auswählen. Das beste Ergebnis ist dann, wenn die 
    Produktbilder keinen Hintegrund haben (3 Beispiele im bin-Ordner). 
  - Baustein nimmt volle Breite: true	
  - Kein Abstand von oben und unten: true
  
2. **Produkte: Kategorie-Boxen** (\QUI\ProductBricks\Controls\CategoryBox)
  - Oberhalb vom Inhalt (Content Prefix)
  - Titel (Frontend): Top Categories
  - Hintergrundfarbe: none
  - Seitenbilder als Hintergrund? true
  - Sortierung: Name (absteigen)
  - Seiten mit Kategorien auswählen: Paar Seiten auswählen. 
    Bei mir habe ich 3 einzelne Kategorie-Seiten: Schuhe, Rucksäcke, Kleidung. Die Seiten sollen passende
    Hintergrundbilder haben (Beispiele in bin-Ordner).
  - URL zu "Alle Kategorien" Seite: Url auswählen
  - Baustein nimmt volle Breite: false	
  - Kein Abstand von oben und unten: false

3. **Produkte: Horizontaler Slider** (\QUI\ProductBricks\Controls\Children\Slider)
  - Oberhalb vom Inhalt (Content Prefix)
  - Titel (Frontend): Product Slider
  - Produkte: Hier paar Produkte zur Liste hinzufügen.
  - Baustein nimmt volle Breite: true/ false	
  - Kein Abstand von oben und unten: false
  