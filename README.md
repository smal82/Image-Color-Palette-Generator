# Image-Color-Palette-Generator

Questa applicazione PHP e jQuery permette di caricare un'immagine e generare una palette dei 20 colori più rilevanti. I colori vengono visualizzati come rettangoli con il valore esadecimale. La palette viene salvata in una directory con nome casuale insieme all'immagine originale.

## Funzionalità
- Carica un'immagine nei formati **JPG**, **JPEG** o **PNG**.
- Estrai i 20 colori unici più rilevanti dall'immagine.
- Visualizza la palette come rettangoli orizzontali con i rispettivi valori esadecimali.
- Cancella automaticamente le cartelle più vecchie di 1 ora nella directory `uploads`.
- Rileva ed evita i colori simili nella palette.
- Visualizza il testo in bianco per colori di sfondo scuri e in nero per colori chiari.
- Usa il font **Open Sans** per visualizzare i valori esadecimali all'interno dell'immagine della palette.

## Installazione

### Requisiti
- PHP 7.x o superiore.
- Libreria GD abilitata in PHP (per l'elaborazione delle immagini).

### Passaggi

1. Clona il repository sulla tua macchina locale:
   ```bash
   git clone https://github.com/smal82/Image-Color-Palette-Generator.git
   ```

2. Assicurati che la directory `uploads` sia scrivibile dal server web:
   ```bash
   chmod 755 uploads
   ```

3. Scarica il font **Open Sans**:
   - Vai su [Google Fonts Open Sans](https://fonts.google.com/specimen/Open+Sans).
   - Scarica il file `.ttf` per **Open Sans** (puoi usare lo stile regolare, ad esempio: `OpenSans-Regular.ttf`).

4. Carica il font nella directory principale del progetto o in una cartella dedicata ai font. Ad esempio, posiziona il file `OpenSans-Regular.ttf` nella directory principale come mostrato:
   ```
   /percorso-progetto/OpenSans-Regular.ttf
   ```

5. Modifica il codice PHP per puntare al font Open Sans. Nel file `process_image.php`, assicurati che questa riga venga aggiornata per riflettere il percorso corretto del font:
   ```php
   $font_path = __DIR__ . '/OpenSans-Regular.ttf'; // Percorso corretto del file Open Sans
   ```

## Utilizzo

1. Carica un'immagine cliccando sul pulsante "Scegli File" e selezionando un'immagine **JPG**, **JPEG** o **PNG**.
2. Una volta caricata l'immagine, lo script elaborerà l'immagine e:
   - Visualizzerà l'immagine originale con un'altezza di 200px.
   - Mostrerà l'immagine della palette generata sotto di essa.
3. L'immagine della palette conterrà rettangoli orizzontali dei 20 colori più rilevanti e unici estratti dall'immagine caricata.
4. Ogni blocco di colore mostrerà il valore esadecimale in **Open Sans**, con testo bianco o nero a seconda della luminosità dello sfondo.

## Personalizzazione

- **Soglia di Similitudine dei Colori:** Puoi regolare la soglia di rilevamento della similitudine tra colori modificando la funzione `colorDistance()` in `process_image.php`. Un valore di soglia più alto comporterà meno colori considerati "simili".
- **Personalizzazione del Font:** Puoi utilizzare altri font `.ttf` caricandoli sul server e aggiornando la variabile `$font_path` nel codice.

## Struttura dei File

```
.
├── index.html                 # Pagina HTML per il frontend
├── process_image.php          # Script PHP per l'elaborazione dell'immagine
├── OpenSans-Regular.ttf       # (File di font caricato)
├── uploads/                   # Directory dove vengono salvate le immagini caricate e le palette generate
└── README.md                  # Questo file
```

## Demo
Visualizza una demo [qui](https://smalprova.netsons.org/palette/)

## Palette d'esempio

![Palette Image](/palette_image.png)

## Licenza

Questo progetto è rilasciato sotto licenza MIT - vedi il file [LICENSE](LICENSE) per i dettagli.

Puoi leggere il testo completo della licenza [qui](https://opensource.org/licenses/MIT).
