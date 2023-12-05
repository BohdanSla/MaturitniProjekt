/*hodnoty do databaze*/
/*muzu dat export z phpmyadmin nebo extensionu!!!!!!!!!!!!!!!!!!!!!*/
INSERT into produkt(nazev,popis,cena,cena_ve_sleve,hodnoceni_produktu,id_znacky,id_sportu,id_typu_produktu) VALUES ("EASTRAIL 2 R.RDY","Dopřejte si na trailu pohodlí. Tyto turistické boty adidas EASTRAIL 2 R.RDY vás v mokrém a promáčeném prostředí udrží v suchu a pohodlí díky technologii RAIN.RDY. Přilnavá podešev Traxion vám na kluzkém a nerovném povrchu dopřeje naprostou jistotu. A to nejlepší? Nepotřebují rozchodit, takže se v nich budete moci vydat rovnou na túru.",1899,NULL,(SELECT COALESCE(AVG(recenze.pocet_hvezd),0) FROM recenze),1,1,1)

INSERT INTO recenze(recenze, pocet_hvezd, id_produktu, id_uzivatele) VALUES ("Vyzerajú dobre, ale vrátené, lebo nesedela veľkosť, napriek tomu, že mám tejto veľkosti už dvojo adidasov. Objednané väčšie...",3,1,2)
/*vyber produktu*/
SELECT produkt.nazev, produkt.popis, produkt.cena, produkt.hodnoceni_produktu, obrazek.src
FROM produkt 
JOIN obrazky_k_produktu ON produkt.id = obrazky_k_produktu.id_produktu
JOIN obrazek ON obrazky_k_produktu.id_obrazku = obrazek.id
LIMIT 4

SELECT produkt.nazev, znacka.nazev, produkt.cena, produkt.cena_ve_sleve, obrazek.src 
FROM produkt, znacka, obrazek 
JOIN obrazky_k_produktu ON obrazky_k_produktu.id_obrazku = obrazek.id 
WHERE znacka.id = produkt.id_znacky 
AND produkt.id = obrazky_k_produktu.id_produktu 
AND produkt.cena_ve_sleve IS NOT NULL;

/*uprava databaze*/
UPDATE produkt JOIN (
	SELECT AVG(recenze.pocet_hvezd) AS prumer
    FROM recenze 
    GROUP BY recenze.id_produktu
    HAVING recenze.id_produktu = 1
) subquery ON 1=1
SET produkt.hodnoceni_produktu = subquery.prumer

SELECT produkt.nazev, produkt.cena, produkt.cena_ve_sleve, GROUP_CONCAT(barva.nazev) AS barvy
FROM produkt,barva
JOIN mnozstvi_produktu_urcite_barvy_a_velikosti ON mnozstvi_produktu_urcite_barvy_a_velikosti.id_produktu = produkt.id
WHERE barva.id = mnozstvi_produktu_urcite_barvy_a_velikosti.id_barvy

SELECT
    obrazek.src
    produkt.nazev,
    produkt.cena,
    produkt.cena_ve_sleve,
    GROUP_CONCAT(barva.nazev) AS barvy
FROM produkt
JOIN mnozstvi_produktu_urcite_barvy_a_velikosti ON mnozstvi_produktu_urcite_barvy_a_velikosti.id_produktu = produkt.id
JOIN barva ON barva.id = mnozstvi_produktu_urcite_barvy_a_velikosti.id_barvy
JOIN 
GROUP BY produkt.id

SELECT produkt.nazev,produkt.popis,produkt.cena,produkt.cena_ve_sleve,produkt.hodnoceni_produktu,znacka.nazev AS znacka,sport.nazev AS sport 
FROM produkt
JOIN znacka ON znacka.id = produkt.id_znacky
JOIN sport ON sport.id = produkt.id_sportu
WHERE produkt.nazev = :nazev

SELECT obrazek.src,produkt.nazev,produkt.popis,produkt.cena,produkt.cena_ve_sleve, kategorie_produktu.kategorie,kategorie_produktu.podkategorie,
znacka.nazev AS znacka,
sport.nazev AS sport
FROM produkt
JOIN kategorie_produktu ON produkt.id_kategorie_produktu = kategorie_produktu.id
JOIN znacka ON znacka.id = produkt.id_znacky
JOIN sport ON sport.id = produkt.id_sportu
JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_produktu
GROUP BY obrazek.id
ORDER BY produkt.nazev ASC;

/*administrace*/
/**//**//**//**//**//**//**//**//**//**//**//**//**//**//**//**//**//**//**/

SELECT produkt.nazev, obrazek.src,produkt.popis,produkt.cena,produkt.cena_ve_sleve,kategorie_produktu.kategorie, kategorie_produktu.podkategorie, 
znacka.nazev AS znacka, 
sport.nazev AS sport FROM produkt JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu JOIN znacka ON znacka.id = produkt.id_znacky JOIN sport ON sport.id = produkt.id_sportu WHERE obrazek.src LIKE "%main%" ORDER BY produkt.nazev ASC;

/*udava pocet barev ke kazdemu produktu*/
SELECT produkt.nazev, COUNT(DISTINCT obrazky_k_produktu.id_barvy) FROM produkt,obrazky_k_produktu,barva WHERE barva.id = obrazky_k_produktu.id_barvy and produkt.id = obrazky_k_produktu.id_produktu GROUP BY produkt.id

SELECT produkt.nazev,material.nazev AS material, materialy_produktu.procento_materialu 
FROM produkt 
JOIN materialy_produktu ON materialy_produktu.id_produktu = produkt.id 
JOIN material ON material.id = materialy_produktu.id_materialu
ORDER BY produkt.nazev ASC

SELECT produkt.nazev,
velikost.nazev AS velikost,
barva.nazev AS barva,
mnozstvi_produktu_urcite_barvy_a_velikosti.pocet AS pocet
FROM produkt
JOIN mnozstvi_produktu_urcite_barvy_a_velikosti ON mnozstvi_produktu_urcite_barvy_a_velikosti.id_produktu = produkt.id
JOIN velikost ON velikost.id = mnozstvi_produktu_urcite_barvy_a_velikosti.id_velikosti
JOIN barva ON barva.id = mnozstvi_produktu_urcite_barvy_a_velikosti.id_barvy
ORDER BY produkt.nazev ASC

SELECT produkt.nazev,
barva.nazev AS barvy,
obrazek.src AS obrazky
FROM produkt
JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id
JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku
JOIN barva ON barva.id = obrazky_k_produktu.id_barvy
ORDER BY produkt.nazev ASC
/**//**//**//**//**//**//**//**//**/


/*lepsi verze selectu produktu-----------------*/
SELECT 
  produkt.nazev,
  obrazek.src,
  produkt.popis,
  produkt.cena,
  produkt.cena_ve_sleve,
  kategorie_produktu.kategorie,
  kategorie_produktu.podkategorie,
  znacka.nazev AS znacka,
  sport.nazev AS sport,
  (
    SELECT COUNT(DISTINCT obrazky_k_produktu.id_barvy) 
    FROM obrazky_k_produktu 
    JOIN barva ON barva.id = obrazky_k_produktu.id_barvy 
    WHERE obrazky_k_produktu.id_produktu = produkt.id
  ) AS barvy_count
FROM 
  produkt 
  JOIN obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id 
  JOIN obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku 
  JOIN kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu 
  JOIN znacka ON znacka.id = produkt.id_znacky 
  JOIN sport ON sport.id = produkt.id_sportu 
WHERE 
  obrazek.src LIKE "%main%" 
ORDER BY 
  produkt.nazev ASC;


/*druha verze*/
  SELECT 
  produkt.nazev,
  obrazek.src,
  produkt.popis,
  produkt.cena,
  produkt.cena_ve_sleve,
  kategorie_produktu.kategorie,
  kategorie_produktu.podkategorie,
  znacka.nazev AS znacka,
  sport.nazev AS sport,
  (
    SELECT COUNT(DISTINCT obrazky_k_produktu.id_barvy) 
    FROM obrazky_k_produktu, barva 
    WHERE barva.id = obrazky_k_produktu.id_barvy 
    AND produkt.id = obrazky_k_produktu.id_produktu 
  ) AS pocet_barev
FROM 
  produkt 
JOIN 
  obrazky_k_produktu ON obrazky_k_produktu.id_produktu = produkt.id 
JOIN 
  obrazek ON obrazek.id = obrazky_k_produktu.id_obrazku 
JOIN 
  kategorie_produktu ON kategorie_produktu.id = produkt.id_kategorie_produktu 
JOIN 
  znacka ON znacka.id = produkt.id_znacky 
JOIN 
  sport ON sport.id = produkt.id_sportu 
WHERE 
  obrazek.src LIKE "%main%" 
ORDER BY 
  produkt.nazev ASC;