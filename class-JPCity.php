<?php

// function vd($data){
//     echo "<pre>";
//     var_dump($data);
//     echo "</pre>";
// }


class JP_City{

    // Parse xml data
    private function get_area_xml(){
        $url      = 'http://weather.livedoor.com/forecast/rss/primary_area.xml';
        $response = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $response;
    }


    // Rest json data
    function get_json($where = null){
        $city_id  = 'city=' . $this->search_id($where);
        $url      = 'http://weather.livedoor.com/forecast/webservice/json/v1?';
        $response = file_get_contents($url . $city_id);
        return $response;
    }


    // get city list array at id or name
    function get_list($type){

        $res    = $this->get_area_xml();
        $cities = $res[0]->xpath('/rss/channel/ldWeather:source');
        $areas  = array();

        if($type == 'id'){

            // each pref, city, id
            foreach($cities[0]->pref as $pref){
                $p = (string)$pref->attributes()->title;

                foreach($pref->city as $city){
                    $c             = (string)$city->attributes()->title;
                    $areas[$p][$c] = (string)$city->attributes()->id;
                }

            }
            return $areas;

        }elseif($type == 'name'){

            // each pref, city, id
            foreach($cities[0]->pref as $pref){
                foreach($pref->city as $city){
                    $c       = (string)$city->attributes()->title;
                    $areas[] = $c;
                }
            }
            return $areas;

        }else{

            $areas[] = 'ERR';
            return $areas;

        }
    }


    // search city id
    function search_id($area = null){

        $match = false;
        $cities = $this->get_list('id');

        foreach($cities as $pref => $city){

            foreach($city as $name => $id){

                if($area == $name){
                    $return = $id;
                    $match  = true;
                    break;
                }

            }

            if($match) break;
        }

        if($match){
            return $return;
        }else{
            return false;
        }

    }

    // formated city list
    function guid_list(){

        $cities    = $this->get_list('id');
        $city_list = '';

        foreach($cities as $pref => $city){

            $city_list .= '【' . $pref . '】';

            foreach($city as $name => $id){
                $city_list .= '　' . $name;
            }

            $city_list .= "\n";

        }

        return $city_list;
    }


} // end class City


?>
