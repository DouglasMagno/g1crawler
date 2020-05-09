<?php
// No site do g1 temos um problema, pois nem todas as noticias são carregadas no html
// por sorte ele carrega elas internamente em um javascript
// mas como pegar?
// bom podemos percorrer e tentar achar os padroes nas strings buscando a tag <a>
$url="https://g1.globo.com/";
$data=file_get_contents($url);
$data = strip_tags($data,"<a>");
$tagsa = preg_split("/<\/a>/",$data);

$links = [];
$counter = 0;
// aqui percorremos e pegamos as tres primeiras
foreach ( $tagsa as $key=>$a ){
	if( strpos($a, "<a href=") != FALSE ){
		$a = preg_replace("/.*<a\s+href=\"/sm","",$a);
		$a = preg_replace("/\".*/","",$a);
		$links[] = $a;
		$counter++;
		if ($counter == 3){
			break;
		}
	}
}
// agora vamos montar nosso json de noticias pegando as demais informacoes
$news = [];
foreach ($links as $index => $link) {
	$n = get_meta_tags($link, true);
	$dom = new DOMDocument;
	libxml_use_internal_errors(true);
	$dom->loadHTML(file_get_contents($link));
	$metaTags = $dom->getElementsByTagName("meta");
	$imageUrl = "";
	foreach ($metaTags as $index => $metaTag) {
		$prop = $metaTag->getAttribute('itemprop');
		if ($prop == "image"){
			$imageUrl = $metaTag->getAttribute('content');
		}
	}
	$news[] = (object)[
		"title" => isset($n['title']) ? $n['title'] : null,
		"description" => isset($n['description']) ? $n['description'] : null,
		"url" => $link,
		"image" => $imageUrl
	];
}
header('Content-Type: application/json');
echo json_encode($news);