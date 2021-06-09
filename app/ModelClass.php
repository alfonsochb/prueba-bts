<?php 
namespace App;


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
class ModelClass
{
	

    private $conn=null;


    const DB_SERVER 	= 'localhost';
    const DB_NAME 		= 'test_meteorology';
    const DB_USER 		= 'root';
    const DB_PASSWORD 	= '';


	function __construct()
	{
        try{
            $opciones = [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
            $this->conn = new \PDO("mysql:host=".self::DB_SERVER.";dbname=".self::DB_NAME, self::DB_USER, self::DB_PASSWORD, $opciones);
            return $this->conn;
        }catch( \PDOException $e ){
            die("Database could not be connected: ".$e->getMessage());
        }
	}


	public function listCities()
	{
        try {
			$result = array();
	        $sql = "SELECT * FROM cities";
	        $query = $this->conn->prepare( $sql );
	        $query->execute();
	        $num = $query->rowCount();
	        $result = $query->fetchAll( \PDO::FETCH_ASSOC );
			return $result;
        } catch (\PDOException $e) {
            return $e->getMessage();
        } 
	}


	public function listHistory( $params=null )
	{
        try {
        	$options = "";
        	if ( isset($params['city_id']) ) {
        		$options.= "AND a.city_id=".$params['city_id']." ";
        	}
        	if ( isset($params['date']) ) {
        		$options.= "AND CAST(a.created_at AS DATE)='".$params['date']."' ";
        	}
        	if ( isset($params['hour']) ) {
        		$options.= "AND HOUR(a.created_at)='".$params['hour']."' ";
        	}
			$result = array();
	        $sql = ("
	        	SELECT a.*, b.* 
	        	FROM weather AS a 
	        	JOIN cities AS b ON b.id=a.city_id 
	        	WHERE a.id IS NOT NULL ".$options
	        );
	        $query = $this->conn->prepare( $sql );
	        $query->execute();
	        $num = $query->rowCount();
	        $result = $query->fetchAll( \PDO::FETCH_ASSOC );
			return $result;
        } catch (\PDOException $e) {
            return $e->getMessage();
        } 
	}


    public function insert( Array $input )
    {
        try {
	        $sql = ("
	            INSERT INTO weather (`city_id`, `timezone`, `latitud`, `longitud`, `forecast`, `description`, `icon`, `temp`, `feels_like`, `temp_min`, `temp_max`, `pressure`, `humidity`, `created_at` )
	            VALUES (:city_id, :timezone, :latitud, :longitud, :forecast, :description, :icon, :temp, :feels_like, :temp_min, :temp_max, :pressure, :humidity, :created_at );
	        ");        	
            $query = $this->conn->prepare($sql);
            $query->execute( $input );
            return $query->rowCount();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }    
    }


}