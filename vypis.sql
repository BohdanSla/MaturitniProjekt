/*hodnoty do databaze*/
/*muzu dat export z phpmyadmin nebo extensionu!!!!!!!!!!!!!!!!!!!!!*/
INSERT into produkt(nazev,popis,cena,cena_ve_sleve,hodnoceni_produktu,id_znacky,id_sportu,id_typu_produktu) VALUES ("EASTRAIL 2 R.RDY","Dopřejte si na trailu pohodlí. Tyto turistické boty adidas EASTRAIL 2 R.RDY vás v mokrém a promáčeném prostředí udrží v suchu a pohodlí díky technologii RAIN.RDY. Přilnavá podešev Traxion vám na kluzkém a nerovném povrchu dopřeje naprostou jistotu. A to nejlepší? Nepotřebují rozchodit, takže se v nich budete moci vydat rovnou na túru.",1899,NULL,(SELECT COALESCE(AVG(recenze.pocet_hvezd),0) FROM recenze),1,1,1)

INSERT INTO recenze(recenze, pocet_hvezd, id_produktu, id_uzivatele) VALUES ("Vyzerajú dobre, ale vrátené, lebo nesedela veľkosť, napriek tomu, že mám tejto veľkosti už dvojo adidasov. Objednané väčšie...",3,1,2)
/*vyber produktu*/
SELECT produkt.nazev, produkt.popis, produkt.cena, produkt.hodnoceni_produktu, obrazek.obrazek_src
FROM produkt 
JOIN obrazky_k_produktu ON produkt.id = obrazky_k_produktu.id_produktu
JOIN obrazek ON obrazky_k_produktu.id_obrazku = obrazek.id
LIMIT 4

SELECT produkt.nazev, znacka.znacka, produkt.cena, produkt.cena_ve_sleve, obrazek.obrazek_src 
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