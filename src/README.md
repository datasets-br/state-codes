
## Preparo das regiões

Na [QueryWikidata](http://query.wikidata.org) a listagem de `wdId` e nome completo de cada região é gerada por:

```sparql
SELECT ?item ?itemLabel 
WHERE {
  ?item wdt:P31 wd:Q753113.
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],pt". }
}
```
onde P31 presenta "instancia de" e a entidade [Q753113](https://www.wikidata.org/wiki/Q753113), da noção de Reião do Brasil, também pode servir de referência para obter a imagem *thumb* das cinco regiões

![](https://upload.wikimedia.org/wikipedia/commons/thumb/f/f6/Brazil_Labelled_Map.svg/200px-Brazil_Labelled_Map.svg.png)


