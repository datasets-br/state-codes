&nbsp; [![goodtables.io](https://goodtables.io/badge/github/datasets-br/state-codes.svg)](https://goodtables.io/github/datasets-br/state-codes) &nbsp; Provided as a [Tabular Data Package](http://frictionlessdata.io/data-packages/): [**view** datapackage](https://data.okfn.org/tools/view?url=https%3A%2F%2Fraw.githubusercontent.com%2Fdatasets-br%2Fstate-codes%2Fmaster%2Fdatapackage.json).  <br/>&nbsp; PS: help  us to view as core dataset at [Brasil.io](http://Brasil.io) and [Datahub.io](http://Datahub.io).

# state-codes

Comprehensive Brazilian state codes information, including ISO 3166-2:BR codes (the official 2-letter code abbreviations), IBGE state codes,  and "timelines" of each state creation. Provided as a Simple Data Format Data Package.

Use [**the last release as the most reliable**](https://github.com/datasets-br/state-codes/releases).

All standard state-codes are at [`br-state-codes.csv`](data/br-state-codes.csv) (described by [`datapackage.json`](datapackage.json)), as "one stop" reliable sourse, and as curated reference to the [digital preservation](https://en.wikipedia.org/wiki/Digital_preservation) of datasets from Wikidata (semantic at [`data/dump_wikidata`](data/dump_wikidata)) and OpenStreetMap (spatial at [`data/dump_osm`](data/dump_osm)), as JSON [dataset **dumps**](https://en.wikipedia.org/wiki/Database_dump).

![](assets/br-states-mapTimeline.png)

Strictly, each code designates an *federation unit* ("Unidade da Federação" - UF), that can be a  [state](https://schema.org/State) as usual  country-scale area segmentation, and can be also [capital districts and territories](https://en.wikipedia.org/wiki/Capital_districts_and_territories).

The time-zone conventions are expressed in state-scale, so they are part of this dataset.

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;![](assets/br-timeZones.png)

## Collaboration

Please, before to submit pull requests for `br-state-codes.csv` changes, **edit [this public spreadsheet](https://docs.google.com/spreadsheets/d/1lwuHtCqAsNGxKs0jsnr8G_KBZ7FXekkHn42dHHKfG4M/)**.
Discussions at [git issues](https://github.com/datasets-br/state-codes/issues) (we can chat Portuguese in there!).

[More details here](collabore.md). Example:

UF | Wikidata link | dump OSM | dump Wikidata 
---|---------------|----------|-----------------------
AC | [acre = Q40780](http://wikidata.org/entity/Q40780) | [AC map](data/dump_osm/AC.geojson) | [AC wd-dump](data/dump_wikidata/AC.json)
AL | [alagoas = Q40885](http://wikidata.org/entity/Q40885) | [AL map](data/dump_osm/AL.geojson) | [AL wd-dump](data/dump_wikidata/AL.json)
...|...|...|...
GB | [*extinct* guanabara = Q1155409](http://wikidata.org/entity/Q1155409) | no map? | [GB wd-dump](data/dump_wikidata/GB.json)
...|...|...|...

So, there are 4 opportunities to collabore:

1. (any one) at the friendly public spreadsheet
2. (github users) here, with issues or pull requests
3. (wiki experts) at Wikidata 
4. (OSM users) at OpenStreetMap

We check here the consistency of all the parts, and we preserve all backups as stable snapshots.

## Preparation

Download the spreadsheet as CSV, and update *git* with it. When editing *datapackage*, test `goodtables datapackage.json` and `git diff` before commit. 

## Sources and references

Primary:

* IBGE, the official authority of names, codes and spatial delimitations: [ibge.gov.br/estadosat](http://www.ibge.gov.br/estadosat/),  [ibge.gov.br/areaterritorial](http://www.ibge.gov.br/home/geociencias/areaterritorial/principal.shtm).

* [LexML.gov.br](http://www.LexML.gov.br) as legislative reference for official documents (mainlly denominations and dates), and the main user of this repository.

* Dataset *dumps* and doble-checking from [Wikidata.org](http://Wikidata.org) and [OpenStreetMap.org](http://OpenStreetMap.org).

* [ISO 3166-2:BR at Wikipedia](https://en.wikipedia.org/wiki/ISO_3166-2:BR).

Secondary and checking:

* [UFs at Wikipedia](https://pt.wikipedia.org/wiki/Unidades_federativas_do_Brasil).

* [`geodata-br` (Free open public domain geographic data of Brazil available in multiple languages and formats)](https://github.com/paulofreitas/geodata-br).
* [`geodata-br GeoJSON` (maps)](https://github.com/tbrugz/geodata-br).

Other references:

* Quality control of datasets at [GoodTables.io](https://goodtables.io) ([this repo](https://goodtables.io/github/datasets-br/state-codes)).

## See also

* [the Wiki of this project](https://github.com/datasets-br/state-codes/wiki)
* [city-codes](http://datasets.OK.org.br/city-codes)
* All dataset ecosystem into one SQL schema: use this dataset with [try-sql-datasets](https://github.com/datasets-br/try-sql-datasets).
