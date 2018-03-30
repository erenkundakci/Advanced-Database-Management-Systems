<!DOCTYPE html>
<html>
  <head>
    <style>
       #map {
        height: 600px;
        width: 100%;
		border: 2px solid;
       }
	   
	   table.blueTable {
		  font-family: Arial, Helvetica, sans-serif;
		  border: 1px solid #1C6EA4;
		  width: 75%;
		  text-align: center;
		  border-collapse: collapse;
		}
		table.blueTable td, table.blueTable th {
		  border: 2px solid #AAAAAA;
		  padding: 10px;
		}
		table.blueTable thead {
		  background: #317CA4;
		  background: -moz-linear-gradient(top, #649dbb 0%, #4589ad 66%, #317CA4 100%);
		  background: -webkit-linear-gradient(top, #649dbb 0%, #4589ad 66%, #317CA4 100%);
		  background: linear-gradient(to bottom, #649dbb 0%, #4589ad 66%, #317CA4 100%);
		  border-bottom: 2px solid #444444;
		}
		table.blueTable thead th {
		  font-weight: bold;
		  color: #FFFFFF;
		  text-align: center;
		  border-left: 2px solid #78D2F5;
		}
		table.blueTable thead th:first-child {
		  border-left: none;
		}
	   
    </style>
  </head>
  <body>
    <?php
      include('dbConfig.php');
    ?>
    <a href="index.php"><h2>Adres Bilgi Sistemi</h2></a>
    <div id="map"></div>
    
    <h3>Sorgulamak istediğiniz alanı seçiniz:</h3>
	
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <?php
    //kişi adı listeleme
      $sql = "SELECT tc, ad, soyad FROM vatandas ORDER BY ad asc";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        echo "Vatandaş Seçiniz: ";
        echo "<select name=\"vatandas\" id=\"vatandas\">";
    		$secim = isset($_POST['vatandas']) ? $_POST['vatandas'] : false;
    		if($secim == null) $secim = $_GET['vat']; 
          while($row = $result->fetch_assoc()) {
			  if($row['ad'] == $secim)
				echo "<option selected value=".$row["ad"].">".$row["ad"]." ".$row["soyad"]."</option>";	
			  else
				echo "<option value=".$row["ad"].">".$row["ad"]." ".$row["soyad"]."</option>";
          }
        echo "</select>";
      }
    //kişi adı listeleme end
    ?>
    <input type="submit" value="Sorgula">
    </form>
	<br><br>
	
    <? 
    $option = isset($_POST['vatandas']) ? $_POST['vatandas'] : false;
	  if($option == null) $option = $_GET['vat']; 
	  $tcSql = "SELECT tc from vatandas WHERE ad = '$option'";
	  $tcSqlResult = $conn->query($tcSql);
	  while ($row2 = $tcSqlResult->fetch_assoc()) {
		$tc = $row2['tc'];
	  }
	  
	if($option != null){
		$sqlAdres = "select * from adres where vatandas_tc = $tc";
		$sqlAdresResult = $conn->query($sqlAdres);
		$row3 = $sqlAdresResult->fetch_assoc();
		$mahalle = isset($row3['mahalle_id']) ? $row3['mahalle_id'] : "----"; //mahalle_id atanmışsa değerini, atanmamışsa ---- değerini atıyoruz.
		$sokak = isset($row3['sokak_id']) ? $row3['sokak_id'] : "----";
		$ilce = isset($row3['ilce_id']) ? $row3['ilce_id'] : "----";
		$il = isset($row3['il_id']) ? $row3['il_id'] : "----";

		//il adını bulma sorgusu
		if($row3['il_id'] != null){
		  $sqlil = "select *, X(konum) as lat, Y(konum) as lng, AsText(alan) as pol from il where id = $il"; 
		  $sqlilResult = $conn->query($sqlil);
		  $rowil = $sqlilResult->fetch_assoc();
		  $ilAdi = $rowil['isim'];
		}else $ilAdi = "----";

		//mahalle adını bulma sorgusu
		if($row3[mahalle_id] != null){
		  $sqlmahalle = "select *, X(konum) as lat, Y(konum) as lng, AsText(alan) as pol from mahalle where id = $mahalle";
		  $sqlmahalleResult = $conn->query($sqlmahalle);
		  $rowmahalle = $sqlmahalleResult->fetch_assoc();
		  $mahalleAdi = $rowmahalle['isim'];
		}else $mahalleAdi = "----";

		//sokak adını bulma sorgusu
		if($row3[sokak_id] != null){
		  $sqlsokak = "select *, X(konum) as lat, Y(konum) as lng, AsText(cizgi) as cizgi from sokak where id = $sokak";
		  $sqlsokakResult = $conn->query($sqlsokak);
		  $rowsokak = $sqlsokakResult->fetch_assoc();
		  $sokakAdi = $rowsokak['isim'];
		}else $sokakAdi = "----";

		//ilçe adını bulma sorgusu
		if($row3[ilce_id] != null){
		  $sqlilce = "select *, X(konum) as lat, Y(konum) as lng, AsText(alan) as pol from ilce where id = $ilce";
		  $sqlilceResult = $conn->query($sqlilce);
		  $rowilce = $sqlilceResult->fetch_assoc();
		  $ilceAdi = $rowilce['isim'];
		}else $ilceAdi = "----";

		$latIl = $rowil['lat'];
		$lngIl = $rowil['lng'];
		$nfsIl = $rowil['nufus'];
		$polIl = $rowil['pol'];

		$latIlce = $rowilce['lat'];
		$lngIlce = $rowilce['lng'];
		$nfsIlce = $rowilce['nufus'];
		$polIlce = $rowilce['pol'];

		$latMahalle = $rowmahalle['lat'];
		$lngMahalle = $rowmahalle['lng'];
		$polMahalle = $rowmahalle['pol'];

		$latSokak = $rowsokak['lat'];
		$lngSokak = $rowsokak['lng'];
		$cizgiSokak = $rowsokak['cizgi'];

		//polygon datasını javascripte aktarmak için yöntem
		echo "<script>";
		echo "var data = ";
		if($_GET['tur'] == 'il')
		echo json_encode($polIl, JSON_HEX_TAG);
		else if($_GET['tur'] == 'ilce')
		echo json_encode($polIlce, JSON_HEX_TAG);
		else if($_GET['tur'] == 'mahalle')
		echo json_encode($polMahalle, JSON_HEX_TAG);
		else echo json_encode("", JSON_HEX_TAG);
		echo ";";
		echo "</script>";
		//polygon datasını javascripte aktarmak için yöntem end

		//linestring datasını javascripte aktarmak için yöntem
		echo "<script>";
		echo "var dataSokak = ";
		if($_GET['tur'] == 'sokak')
		echo json_encode($cizgiSokak, JSON_HEX_TAG);
		else echo json_encode("", JSON_HEX_TAG);
		echo ";";
		echo "</script>";
		//linestring datasını javascripte aktarmak için yöntem end
		 
		//il id databasede 06 yerine 6 olarak tutulduğundan, 2 haneli gözükmesi için formatlıyoruz
		$num = $il;
		$str_length = 2;
		$ilFormatted = substr("00{$num}", -$str_length);

		if($_POST['vatandas'] || $_GET['vat']){
			echo("<table align=\"center\" class=\"blueTable\">
				<thead>
				<tr>
				<th>TC</th>
				<th>İl</th>
				<th>İlçe</th>
				<th>Mahalle</th>
				<th>Sokak/Cadde</th>
				</tr>
				</thead>
				<tbody>
				<tr>
				<td>$tc</td>
				<td><a href=\"index.php?vat=$option&tur=il&lat=$latIl&lng=$lngIl&nfs=$nfsIl\">$ilAdi($ilFormatted)</a></td>
				<td><a href=\"index.php?vat=$option&tur=ilce&lat=$latIlce&lng=$lngIlce&nfs=$nfsIlce\">$ilceAdi</a></td>
				<td><a href=\"index.php?vat=$option&tur=mahalle&lat=$latMahalle&lng=$lngMahalle\">$mahalleAdi</a></td>
				<td><a href=\"index.php?vat=$option&tur=sokak&lat=$latSokak&lng=$lngSokak\">$sokakAdi</a></td>
				</tr>
				</tbody>
				</table>");
		}
	}
    ?>

	<script>
	  function getUrlVars() {
		var vars = {};
		var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,    
		function(m,key,value) {
		  vars[key] = value;
		});
		return vars;
	  }
	  		
      function initMap() {
		var vlat = parseFloat(getUrlVars()["lat"]);
		var vlng = parseFloat(getUrlVars()["lng"]);
		var vnfs = getUrlVars()["nfs"];
		var zoomBelirle = getUrlVars()["tur"];
		var zum = 6;
		
		switch(zoomBelirle) {
			case 'il':
				zum = 8;
				break;
			case 'ilce':
				zum = 10;
				break;
			case 'mahalle':
				zum = 15;
				break;
			case 'sokak':
				zum = 17;
				break;
			default:
				zum = 6;
		}
		
		if(!vlat || !vlng){
			vlat = 38.9597594;
			vlng = 34.9249653;
			vnfs = '80.810.525';
		} 
		
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: zum,
          center: {lat: vlat, lng: vlng},
        });
		
		//PHP'den JavaScript'e aktarılan Polygon verisinin uygun formata sokulması:
		data = data.substr(0, data.length-2); //Sonunda 2 parantez var, o siliniyor.
		data = data.substr(9); //Başında POLYGON(( yazısı var, toplam 9 karakter siliniyor.
		var splitData = data.split(","); //Elde kalan koordinat bilgileri, aralarındaki virgüllerden parçalanarak bir diziye atanıyor.
		
		//Sorgusu yapılan alanın türü il, ilçe,mahalleden biriyse, polygon gösterme fonksiyonu çalışıyor
		if(getUrlVars()["tur"] == "il" || getUrlVars()["tur"] == "ilce" || getUrlVars()["tur"] == "mahalle"){
			var polygonCoords = [];
			for(i = 0; i < splitData.length; i++){
				var latlng = splitData[i].split(" ");
				var polygonCoordObj = {lat: parseFloat(latlng[1]), lng: parseFloat(latlng[0])}; //DB'de ters tutuluyorlar önce lng sonra lat şeklinde, o yüzden ters ekliyoruz
				polygonCoords[i] = polygonCoordObj;
			}
			
			var polygonGoster = new google.maps.Polygon({
			  paths: polygonCoords,
			  strokeColor: '#FF0000',
			  strokeOpacity: 0.8,
			  strokeWeight: 2,
			  fillColor: '#FF0000',
			  fillOpacity: 0.35
			});
			polygonGoster.setMap(map);
		}
		
		//PHP'den JavaScript'e aktarılan LineString bilgisinin uygun formata sokulması:
		dataSokak = dataSokak.substr(0, dataSokak.length-1); //Sonunda 1 parantez var, o siliniyor.
		dataSokak = dataSokak.substr(11); //Başında LINESTRING( yazısı var, toplam 11 karakter siliniyor.
		var splitDataSokak = dataSokak.split(","); //Elde kalan koordinat bilgileri, aralarındaki virgüllerden parçalanarak bir diziye atanıyor.
		
		//Sorgusu yapılan alanın tür bilgisi sokak ise çizgi çizme fonksiyonu çalışıyor.		
		if(getUrlVars()["tur"] == "sokak"){ 		
			var cizgiCoords = [];
			for(i = 0; i < splitDataSokak.length; i++){
				var latlng = splitDataSokak[i].split(" ");
				var cizgiCoordObj = {lat: parseFloat(latlng[1]), lng: parseFloat(latlng[0])}; //DB'de ters tutuluyorlar önce lng sonra lat şeklinde, o yüzden ters ekliyoruz
				cizgiCoords[i] = cizgiCoordObj;
			}
			
			var sokakCiz = new google.maps.Polyline({
			  path: cizgiCoords,
			  geodesic: true,
			  strokeColor: '#FF0000',
			  strokeOpacity: 1.0,
			  strokeWeight: 2
			});
			sokakCiz.setMap(map);
		}
		
        var marker = new google.maps.Marker({ //Haritaya işaretçi koyma fonksiyonu
          position: {lat: vlat, lng: vlng},
          map: map,
		  title: 'Nüfus: ' + vnfs
        });
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC0e9Gln1MRGKNzrJaaphvV7CB2qlcgwmw&callback=initMap">
    </script>
  </body>
</html>