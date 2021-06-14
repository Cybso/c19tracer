# c19tracer

Corona Kontakterfassung für Universum e.V.

## Hinweis

Dies ist ein schnell gehacktes Tool als Alternative
zur Luca-App für einen kleinen Verein.

Das Ziel war es, die Kontaktdaten der Besucher ohne die Notwendigkeit
einer App-Installation digital erfassen zu können, wie es gemäß
der aktuellen Version der Niedersächsischen Corona-Verordnung
vorgeschrieben ist. Der Aufruf der Seite kann per QR-Code erfolgen.
Zur Verbesserung des Komforts können die Eingabedaten gespeichert
werden, dies erfolgt jedoch ausschließlich im localStorage des Browser.

Es werden nur die notwendigen Daten erfasst und tageweise mit einem
Zeitstempel versehen in eine CSV-Datei gespeichert, wobei zuvor
jede Zeile separat per GnuPG asymetrisch verschlüsselt wird. Sofern
der private Schlüssel ordnungsgemäß geheim gehalten wird, ist es
also selbst für einen Angreifer mit Lese-Zugriff auf den Server
nicht möglich, die Dateien auszulesen.

Für die Entschlüsselung der Daten steht ein kleines JavaScript-Tool
zur Verfügung (decrypt.html), welches jedoch komplett lokal arbeitet.

Wie bei der Luca-App ist es auch hier möglich, die Veranstaltung
mit falschen Kontaktdaten zu fluten, dies ist bei zentralen
Kontakterfassungstools ein grundsätzliches Problem. Um dieses
Problem ein wenig zu mitigieren besteht die Option, jeder Veranstaltung
ein individuelles "token" als Query-Parameter mitzugeben. Dies wird
mit in die CSV-Datei geschrieben, so dass hinter eine Filterung 
möglich ist.


## Installation

1. Layout und Text anpassen
2. Ein GnuPG-Schlüsselpaar erzeugen, falls noch nicht vorhanden. Den öffentlichen Schlüssel exportieren.
3. Den öffentlichen Schlüssel in die lokale Konfigurationsdatei importieren:

```
	gpg --homedir=covid19.gnupg --import -a < public-key.txt
```

4. Die ID des importieren Schlüssels in 'encrypt.php' eintragen. Bei der Gelegenheit auch gleich die Pfade zu "covid19.data" und "covid19.gnupg" prüfen.
5. Das ganze auf einen entsprechenden Webserver kopieren, auf dem PHP und GnuPG zur Verfügung stehen.
6. Einen Cronjob einrichten, der täglich das Programm "cleanup.php" aufruft, um die abgelaufenen Daten zu beseitigen.

## Entschlüsselung der Daten

GnuPG kann nicht mehrere verschlüsselte Blöcke innerhalb der selben Datei entschlüsseln, daher ist die 
Nutzung eines kleinen Tools erforderlich. Dieses Projekt stellt dafür das JavaScript 'decrypt.html'
zur Verfügung.

Vorgehen:

1. Den privaten GnuPG-Schlüssel im ASCII-Format exportieren:

```
	gpg -a --export-secret-keys KEYID
```

2. Die verschlüsselte Datei des vom Gesundheitsamt angeforderten Tages per (S)FTP herunterladen

3. Den Seite "decrypt.html" aufrufen

4. Den Inhalt der verschlüsselten Datei sowie den privaten Schlüssel in die jeweiligen Textboxen kopieren

5. Das Passwort des privaten Schlüssels eingeben und bestätigen

6. Den entschlüsselten Inhalt der Datei ggfs. nach Zeitraum und Token filtern und an das Gesundheitsamt weitergeben.

## Best practises

Ich empfehle, für das Tool ein individuell erzeugtes Schlüsselpaar zu erzeugen und den 
privaten Schlüssel sowie das dazu notwendige Passphrase an unterschiedliche Personen
aushändigen, so dass immer mindestens zwei Personen erforderlich sind, um auf
die Daten zugreifen zu können.

## TODO

Das Entschlüsselungstool komfortabler machen. Das Projekt gehe ich an, sobald das Gesundheitsamt
mehr als zwei mal angeklopft hat. Im besten Fall wird es sowieso nie gebraucht...
