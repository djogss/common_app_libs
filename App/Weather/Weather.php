<?php
class App_Weather_Weather
{
    public function getWeather()
    {
        $path = $_SERVER['SCRIPT_NAME'];
        $path = explode('/', $path);
        $path = substr($_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['SCRIPT_NAME']) - strlen($path[count($path)-1])); 
        $tplpath  = $path;
        try{
            $xml = new App_Weather_ParseXml();
            $yahoo_chanel_output = $xml->GetXMLTree("http://xml.weather.yahoo.com/forecastrss?p=LHXX0009&u=f");
            $simplify_chanel = $yahoo_chanel_output['RSS'][0]['CHANNEL'][0]['ITEM'][0]['YWEATHER:FORECAST'];
            
            for($j = 0; $j < 2; $j++){
                if($simplify_chanel[$j]['ATTRIBUTES']['TEXT'] == "Thundershowers"){
                    $simplify_chanel[$j]['ATTRIBUTES']['TEXT'] = "Thunder";
                }
                $tomorrow = mktime(0, 0, 0, date("m"), date("d")+$j, date("y"));
                $data[$j] = array(
                                    "day_name"   => $simplify_chanel[$j]['ATTRIBUTES']['DAY'],
                                    "temp_name"  => str_replace("/", "/<br />", $simplify_chanel[$j]['ATTRIBUTES']['TEXT']),
                                    "low_temp"   => round(($simplify_chanel[$j]['ATTRIBUTES']['LOW']-32) * 5/9),
                                    "high_temp"  => round(($simplify_chanel[$j]['ATTRIBUTES']['HIGH']-32) * 5/9),
                                    "code"       => $simplify_chanel[$j]['ATTRIBUTES']['CODE'] ,
                                    "date"       => date("D", $tomorrow) 
                                  );
                                
            }
            return $data;
        }
          catch (Exception $e) {
              $log->log($e, 1);
          }
    }
}
?>
