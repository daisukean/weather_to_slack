<?php if($_POST){

// init
require('class-JPCity.php');
$city = new JP_City();
$jp_week = array('日', '月', '火', '水', '木', '金', '土');

// remake input data
$str = array_values(array_filter(preg_split('/[ 　]/u', $_POST['text'])));
if(($key = array_search('天気', $str)) !== false){

    // get available city list
    $city_list = $city->get_list('name');

    // delete keyword(only where,when)
    unset($str[$key]);

    // diff
    $where = array_values(array_intersect($str, $city_list));
    $when  = array_values(array_diff($str, $city_list));

    // date is empty when set description
    if(count($when) == 0) $description = true;

}


// check many location & date or no date
if($city->search_id($where[0]) !== false){

    $response = $city->get_json($where[0]);

    // check data available
    if ($response && ($weather = json_decode($response))) {

        $username = '天気予報';

        if(!$description){
            foreach($weather->forecasts as $forecast) {

                if ($forecast->dateLabel == $when[0]) {

                    $icon_url = $forecast->image->url;
                    $time = strtotime($forecast->date);
                    $week = '（'.$jp_week[date('w', $time)].'） ';
                    $date = date('n月j日'.$week , $time);
                    $location = $weather->location->city . 'の天気';

                    $text .= ':elephant: ' . $date . $location . ' :elephant:' . "\n\n"
                          . $forecast->telop . "　";

                    if ($forecast->temperature->min && $forecast->temperature->max){
                        $text .= $forecast->temperature->max->celsius . '℃/'
                              . $forecast->temperature->min->celsius . '℃';
                    }

                    echo json_encode(
                        array(
                            'text' => $text,
                            'username' => $username,
                            'icon_url' => $icon_url
                        )
                    );

                    return;
                }
            }
        }else{

            $icon_url = $weather->forecasts[0]->image->url;
            $text = $weather->description;
            
            echo json_encode(
                array(
                    'text' => $text,
                    'username' => $username,
                    'icon_url' => $icon_url
                )
            );

        } // if description is not true

    }else{
        $errstr = 'データの取得に失敗しました。';
        echo json_encode(
            array('text' => $errstr)
        );
    }
}else{
    $errstr = ':japan: 利用できるのは以下のエリアです :japan:' . "\n\n";
    $errstr .= $city->guid_list();
    echo json_encode(
        array('text' => $errstr)
    );
}




}else{
    echo  '404';
} // if($_POST[])

?>
