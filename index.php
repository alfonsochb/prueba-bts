<?php
require_once 'vendor/autoload.php';

use Config\AppConfig as Config;
use App\ModelClass;


/**
 * @api Ejemplo sencillo de consumir recursos del API "OpenWeather".
 * @category Prueba desarrollador PHP senior "BROWSER TRAVEL SOLUTIONS"
 * @author Ing. Alfonso Chávez Baquero <alfonso.chb@gmail.com>
 * @since Creado: 2021-06-08
 * @see Referencias:
 * @link https://www.youtube.com/watch?v=xJ5cux3b2gQ
 * @link https://openweathermap.org/weather-conditions
 * @link https://weatherstack.com/?fpr=geekflare
 * @link https://geekflare.com/es/weather-api/
 */
class ClassMetereologica extends ModelClass
{

    public function currentDate()
    {
        $meses = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
        $dias = ['1' => 'lunes', '2' => 'martes', '3' => 'miercoles', '4' => 'jueves', '5' => 'viernes', '6' => 'sábado', '7' => 'domingo'];
        return ucfirst(strtolower($dias[date('N')])).' '.date('d').' de '.$meses[date('m')].' de '.date('Y');
    }


   /**
     * Método para obtener datos desde las API Restfull.
     * @param (array) $params - Los parametros requeridos por el API Restfull.
     * @return (array structure) - Una estructura de datos.
     */
    public function curlRequestJson( $params='' )
    {
       	$config = new Config();
       	$params = array_merge(['appid' => $config->apiKey], $params);
        $endpoint = $config->endPoint . "?" . http_build_query($params);
        //die( $endpoint ); 

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);// SSL: certificate
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 80);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if( curl_error($curl) ){
            return ['error' => 'Request Error: '.curl_error($curl)];
        }
        curl_close($curl);
        //echo "<pre>"; print_r($response); die("<br>Debug con status: ".$status);

        $response = strip_tags( $response );
        return json_decode($response, true); // Array PHP.
    }


    public function currentWeatherData()
    {
    	$array_data = [];
    	$cities = $this->listCities();
		foreach ($cities as $key => $city) {
			$params = [
				'q' => $city['name'],
				'lang' => 'es',
				'units' => 'metric'
			];
			$response = $this->curlRequestJson( $params );
			//echo "<pre>=="; print_r($response); die;
			$info = [
				'city_id' => $city['id'],
				'name' => $response['name'],
				'country' => $response['sys']['country'],
				'timezone' => $response['timezone'],
				'latitud' => $response['coord']['lat'],
				'longitud' => $response['coord']['lon'],
				'forecast' => $response['weather'][0]['main'],
				'description' => $response['weather'][0]['description'],
				'icon' => $response['weather'][0]['icon']
			];
			$aux = array_merge($info, $response['main']);
			array_push($array_data, (object)$aux);
		}
    	return $array_data;
    }


    public function addHistory( $array_data=[] )
    {
    	if ( is_array($array_data) and !empty($array_data) ) {
    		foreach ($array_data as $key => $info) {
		    	$list = $this->listHistory([
		    		'city_id' => $info->city_id,
		    		'date' => date("Y-m-d"),
		    		'hour' => date("H")
		    	]);
		    	//echo "<pre>"; print_r($info); print_r($list);
		    	if ( !is_array($list) or empty($list) ) {
			    	$add = $this->insert([
			    		'city_id' => $info->city_id,
			    		'timezone' => $info->timezone,
			    		'latitud' => $info->latitud,
			    		'longitud' => $info->longitud,
			    		'forecast' => $info->forecast,
			    		'description' => $info->description,
			    		'icon' => $info->icon,
			    		'temp' => $info->temp,
			    		'feels_like' => $info->feels_like,
			    		'temp_min' => $info->temp_min,
			    		'temp_max' => $info->temp_max,
			    		'pressure' => $info->pressure,
			    		'humidity' => $info->humidity,
			    		'created_at' => date("Y-m-d H:i:s")
			    	]);	

		    	}
    		}
    	}
    	return;
    }


    public function getHistorical()
    {
    	return $this->listHistory();
    	//...
    }	
    

}

$obj = new ClassMetereologica();
$current_date 	= $obj->currentDate();
$current_data 	= $obj->currentWeatherData();
$add 			= $obj->addHistory( $current_data );
$historical 	= $obj->getHistorical();
?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Proyecto Ejemplo</title>
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
		<link rel="shortcut icon" href="https://getbootstrap.com/docs/5.0/assets/brand/bootstrap-logo.svg">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="./public/css/app.css">
		<style type="text/css">#chartdiv {width: 100%;height: 500px}</style>
	</head>
	<body>
		
		<header>
			<div class="container">
				<nav class="navbar navbar-light bg-white shadow px-3 border border-white mb-5">
					<a class="navbar-brand" href="#">
						<img src="https://getbootstrap.com/docs/5.0/assets/brand/bootstrap-logo.svg" alt="" width="30" height="24" class="d-inline-block align-top">
						<?=$current_date?>
					</a>
				</nav>
			</div>
		</header>

		<main>
			<div class="container">

				<div class="row row-cols-1 row-cols-md-3 mb-4 text-center">
					<?php foreach ($current_data as $key => $info): ?>
						<div class="col">
							<div class="card mb-4 border border-white rounded-3 shadow">
								<div class="card-body bg-card">
									<h4 class="mb-3 fw-normal"><?=$info->country.' - '.$info->name?></h4>
									<h1 class="card-title pricing-card-title">
										<img src="<?='http://openweathermap.org/img/wn/'.$info->icon.'@2x.png'?>" width="50px" height="50px" alt="">
										<small class="text-muted fw-light"><?=number_format($info->temp, 0, '.', '').'°C'?></small>
									</h1>
									<ul class="list-unstyled mt-3 mb-4">
										<li><b><?=$info->description?></b></li>
										<li><?='Min: '.number_format($info->temp_min, 0, '.', '').'°C / Max: '.number_format($info->temp_max, 0, '.', '').'°C'?></li>
										<li><?='Humedad: '.number_format($info->humidity, 0, '.', '').'%'?></li>
									</ul>
									<!--<button type="button" class="btn btn-sm btn-outline-primary">Ver datos</button>-->
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="card bg-white shadow">
					<h5 class="card-header">Información meteorológica</h5>
					<div class="card-body p-2">
						
						<div id="chartdiv"></div>

						<div class="table-responsive my-5" style="max-height: 20rem;">
							<table class="table table-bordered table-hover bg-transp">
								<thead>
									<tr>
										<th scope="col">Fecha</th>
										<th scope="col">Ciudad</th>
										<th scope="col">Clima</th>
										<th scope="col">Temperatura</th>
										<th scope="col">Mínima</th>
										<th scope="col">Máxima</th>
										<th scope="col">Humedad</th>
									</tr>
								</thead>
								<tbody>
									<?php 
									foreach ($historical as $key => $row): 
										$info = (object)$row;
										?>
										<tr>
											<td><?=$info->created_at?></td>
											<th scope="row"><?=$info->country.' - '.$info->name?></th>
											<td><?=$info->description?></td>
											<td><?=number_format($info->temp, 0, '.', '').'°C'?></td>
											<td><?=number_format($info->temp_min, 0, '.', '').'°C'?></td>
											<td><?=number_format($info->temp_max, 0, '.', '').'°C'?></td>
											<td><?=number_format($info->humidity, 0, '.', '').'%'?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

					</div>
				</div>

			</div>
		</main>

		<footer class="mb-5 pt-5 text-muted text-center text-small">
			<div class="container">
				<p class="mb-1"><?='&copy; '.date("Y").' – Ing. Alfonso Chávez Baquero'?></p>
			</div>
		</footer>

	    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>
	    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js" integrity="sha384-nsg8ua9HAw1y0W1btsyWgBklPnCUAFLuTMS2G72MMONqmOymq585AcH49TLBQObG" crossorigin="anonymous"></script>

		<!-- Resources -->
		<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
		<script src="https://cdn.amcharts.com/lib/4/maps.js"></script>
		<script src="https://cdn.amcharts.com/lib/4/geodata/usaLow.js"></script>
		<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

		<!-- Chart code -->
		<script type="text/javascript">
			am4core.ready(function() {

				// Themes begin
				am4core.useTheme(am4themes_animated);
				// Themes end

				// Create map instance
				var chart = am4core.create("chartdiv", am4maps.MapChart);

				// Set map definition
				chart.geodata = am4geodata_usaLow;

				// Set projection
				chart.projection = new am4maps.projections.AlbersUsa();

				// Create map polygon series
				var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());

				//Set min/max fill color for each area
				polygonSeries.heatRules.push({
					property: "fill",
					target: polygonSeries.mapPolygons.template,
					min: chart.colors.getIndex(1).brighten(1),
					max: chart.colors.getIndex(1).brighten(-0.3)
				});

				// Make map load polygon data (state shapes and names) from GeoJSON
				polygonSeries.useGeodata = true;

				// Set heatmap values for each state
				polygonSeries.data = [
					{
						id: "US-AL",
						value: 4447100
					},
					{
						id: "US-AK",
						value: 626932
					},
					{
						id: "US-AZ",
						value: 5130632
					},
					{
						id: "US-AR",
						value: 2673400
					},
					{
						id: "US-CA",
						value: 33871648
					},
					{
						id: "US-CO",
						value: 4301261
					},
					{
						id: "US-CT",
						value: 3405565
					},
					{
						id: "US-DE",
						value: 783600
					},
					{
						id: "US-FL",
						value: 15982378
					},
					{
						id: "US-GA",
						value: 8186453
					},
					{
						id: "US-HI",
						value: 1211537
					},
					{
						id: "US-ID",
						value: 1293953
					},
					{
						id: "US-IL",
						value: 12419293
					},
					{
						id: "US-IN",
						value: 6080485
					},
					{
						id: "US-IA",
						value: 2926324
					},
					{
						id: "US-KS",
						value: 2688418
					},
					{
						id: "US-KY",
						value: 4041769
					},
					{
						id: "US-LA",
						value: 4468976
					},
					{
						id: "US-ME",
						value: 1274923
					},
					{
						id: "US-MD",
						value: 5296486
					},
					{
						id: "US-MA",
						value: 6349097
					},
					{
						id: "US-MI",
						value: 9938444
					},
					{
						id: "US-MN",
						value: 4919479
					},
					{
						id: "US-MS",
						value: 2844658
					},
					{
						id: "US-MO",
						value: 5595211
					},
					{
						id: "US-MT",
						value: 902195
					},
					{
						id: "US-NE",
						value: 1711263
					},
					{
						id: "US-NV",
						value: 1998257
					},
					{
						id: "US-NH",
						value: 1235786
					},
					{
						id: "US-NJ",
						value: 8414350
					},
					{
						id: "US-NM",
						value: 1819046
					},
					{
						id: "US-NY",
						value: 18976457
					},
					{
						id: "US-NC",
						value: 8049313
					},
					{
						id: "US-ND",
						value: 642200
					},
					{
						id: "US-OH",
						value: 11353140
					},
					{
						id: "US-OK",
						value: 3450654
					},
					{
						id: "US-OR",
						value: 3421399
					},
					{
						id: "US-PA",
						value: 12281054
					},
					{
						id: "US-RI",
						value: 1048319
					},
					{
						id: "US-SC",
						value: 4012012
					},
					{
						id: "US-SD",
						value: 754844
					},
					{
						id: "US-TN",
						value: 5689283
					},
					{
						id: "US-TX",
						value: 20851820
					},
					{
						id: "US-UT",
						value: 2233169
					},
					{
						id: "US-VT",
						value: 608827
					},
					{
						id: "US-VA",
						value: 7078515
					},
					{
						id: "US-WA",
						value: 5894121
					},
					{
						id: "US-WV",
						value: 1808344
					},
					{
						id: "US-WI",
						value: 5363675
					},
					{
						id: "US-WY",
						value: 493782
					}
				];

				// Set up heat legend
				let heatLegend = chart.createChild(am4maps.HeatLegend);
				heatLegend.series = polygonSeries;
				heatLegend.align = "right";
				heatLegend.valign = "bottom";
				heatLegend.width = am4core.percent(20);
				heatLegend.marginRight = am4core.percent(4);
				heatLegend.minValue = 0;
				heatLegend.maxValue = 40000000;

				// Set up custom heat map legend labels using axis ranges
				var minRange = heatLegend.valueAxis.axisRanges.create();
				minRange.value = heatLegend.minValue;
				minRange.label.text = "Little";
				var maxRange = heatLegend.valueAxis.axisRanges.create();
				maxRange.value = heatLegend.maxValue;
				maxRange.label.text = "A lot!";

				// Blank out internal heat legend value axis labels
				heatLegend.valueAxis.renderer.labels.template.adapter.add("text", function(labelText) {
					return "";
				});

				// Configure series tooltip
				var polygonTemplate = polygonSeries.mapPolygons.template;
				polygonTemplate.tooltipText = "{name}: {value}";
				polygonTemplate.nonScalingStroke = true;
				polygonTemplate.strokeWidth = 0.5;

				// Create hover state and set alternative fill color
				var hs = polygonTemplate.states.create("hover");
				hs.properties.fill = am4core.color("#3c5bdc");

			}); // end am4core.ready()
		</script>

  </body>
</html>