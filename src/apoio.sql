/**
 * Consultas sobre o shape IBGE.
 * Introduzido por https://github.com/datasets-br/state-codes/issues/22
 * @see ftp://geoftp.ibge.gov.br/organizacao_do_territorio/malhas_territoriais/malhas_municipais/municipio_2017/Leia_me_Historico_Malha%20Digital.pdf
 * @see ftp://geoftp.ibge.gov.br/organizacao_do_territorio/malhas_territoriais/malhas_municipais/municipio_2017/Brasil/BR/br_unidades_da_federacao.zip
 */

CREATE SCHEMA IF NOT EXISTS lib;

-- conversão de código IBGE de UF para sogla de UF:
CREATE or replace FUNCTION lib.id_ibge2uf(p_id text) REtURNS text AS $$
  SELECT ('{
    "12":"AC", "27":"AL", "13":"AM", "16":"AP", "29":"BA", "23":"CE",
    "53":"DF", "32":"ES", "52":"GO", "21":"MA", "31":"MG", "50":"MS",
    "51":"MT", "15":"PA", "25":"PB", "26":"PE", "22":"PI", "41":"PR",
    "33":"RJ", "24":"RN", "11":"RO", "14":"RR", "43":"RS", "42":"SC",
    "28":"SE", "35":"SP", "17":"TO"
  }'::jsonb)->>$1
$$ language SQL immutable;


CREATE VIEW vw_brufe250gc_borders AS
  -- Lista de estados com que cada um faz fronteira:
  SELECT a_nm, array_to_string(array_agg(b_nm),' ') borders
  FROM (
    SELECT DISTINCT a_nm, b_nm
    FROM (
       SELECT lib.id_ibge2uf(a.cd_geocuf) a_nm,
         lib.id_ibge2uf(b.cd_geocuf) b_nm,
         ST_Relate(a.geom,b.geom) rel
       FROM brufe250gc_sir a, brufe250gc_sir b
       WHERE a.cd_geocuf!=b.cd_geocuf AND a.geom && b.geom
    ) t
  WHERE rel!='FF2FF1212'
  ORDER BY 1,2) tt  group by 1 order by 1
;

CREATE VIEW vw_brufe250gc_newcols AS
  SELECT uf, km2, centroid_geohash, utm_zones,
         gh_bd[1] ||' '|| gh_bd[3] bounds_geohash,
         (b->>'minlat')||' '||(b->>'maxlat') bounds_lat,
         (b->>'minlon')||' '||(b->>'maxlon') bounds_long
  FROM (
    SELECT lib.id_ibge2uf(cd_geocuf) uf,
           round(st_area(geom,true)/1000000.0) km2,
           ST_Geohash(st_centroid(geom),9) centroid_geohash,
           array_to_string(get_utmzone_names(geom),' ') utm_zones ,
           ST_Extent_Geohash(geom,9) gh_bd,
           ST_Extent_jsonb(geom) b
    FROM brufe250gc_sir order by 1
  ) t
;
-----

/* para análise:

SELECT *, lib.ST_Relate_summary(rel) rel_descr
-- https://github.com/ppKrauss/postgis-st-relate-summary
FROM (
  SELECT a.nm_estado,b.nm_estado, ST_Relate(a.geom,b.geom) rel
  FROM brufe250gc_sir a, brufe250gc_sir b
  WHERE a.cd_geocuf>b.cd_geocuf AND a.geom && b.geom
) t WHERE rel!='FF2FF1212'
ORDER BY 1,2,3
; -- 56 casos, com st_buffer(a.geom,0.0001) são 51 e apenas entre áreas, '212101212'.
*/
