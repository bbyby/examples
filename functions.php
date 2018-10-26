<?php

/**
 *  Функция создания лида в битрикс24
 *  
 *  @param array $post Массив с параметрами таблички
 *  @return string 
 **/
function bitrix24_add($post) {
	$formID = [
		'form_code' => '8',
		'sec' => 'XXX'
	];
	$CRM_FORM_URL = 'https://XXX.bitrix24.ru/pub/form/'.
                    $formID['form_code'].'/'.$formID['sec'].'/?';
    $host = $_SERVER['HTTP_HOST'];
    $name = trim($post['fld_name']);
    $lead_title = "Заявка с сайта [".$host."]".(!empty($name)?' - '.$name:'');
  
    $lead_source = $host;
  
    $array_utm = array();
    foreach($post as $k=>$v){
        if(stripos($k, 'utm_') !== false && !empty($v)){
            $array_utm[$k] = $v;
        }
    }
  
    $metall_ARR = array(
        '86' => 'Латунь',
        '88' => 'Алюминий',
        // '90' => 'Бронза'
    );
  
  // Текстура букв   
  $surface_ARR = array(
        '92' => 'Кожа',
        '94' => 'Орех',
        '96' => 'Мрамор',
        '98' => 'Низкий рельеф',
    );
  
    // Поверхность букв:
    $gloss_ARR = array(
        '100' => 'Матовый',
        '102' => 'Глянец',
    );
  
    $color_ARR = array(
        '104' => 'Коричневый + золотая патина',
        '106' => 'Синий',
        '108' => 'Зеленый',
        '110' => 'Черный + золотая патина',
        '112' => 'Коричневый',
        '114' => 'Черный + медная патина',
        '116' => 'Черный + серебряная патина',
        '118' => 'Черный',
        '120' => 'Серый',
        '122' => 'из латуни + чернение под старину',
    );
  
    $size_ARR = array(
        '130' => 'малый',
        '132' => 'средний',
        '134' => 'большой',
    );
  
    $post_data = array(
        'LEAD_TITLE'               => $lead_title,
        'LEAD_NAME'                => $name,
        'LEAD_SOURCE_ID'           => 'WEB',
        'LEAD_SOURCE_DESCRIPTION'  => $lead_source,
        'LEAD_PHONE'               => preg_replace("/[^0-9+]/","",$post['fld_tel']),
        'LEAD_EMAIL'               => $post['fld_email'],
        'from'                     => $CRM_FORM_URL . http_build_query($array_utm),

        // Артикул
        'LEAD_UF_CRM_59BADE3D9E995' => $post['fld_sku'],
    );
  
    // Сообщение клиента:
    if(!empty($post['fld_dop'])){
        $post_data['LEAD_UF_CRM_59BAD779081E0'] = $post['fld_dop'];
    }
    
    // Текст на табличке:
    if(!empty($post['fld_adr'])){
        $post_data['LEAD_UF_CRM_59B96F1A39281'] = $post['fld_adr'];
    }

    // Металл
    if(!empty($post['fld_metal'])){
        foreach($metall_ARR as $k=>$v){
            $mask = preg_quote(substr($v, 0, -2), '/');
            if(preg_match("/($mask)/imu", $post['fld_metal'])){
                $post_data['LEAD_UF_CRM_59BADE3DA8F5A'] = $k;
                break;
            }
        }
    }

    // Фактура фона:
    if(!empty($post['fld_surface'])){
        foreach ($surface_ARR as $k=>$v) {
            $mask = preg_quote($v, '/');
            if(preg_match("/($mask)/imu", $post['fld_surface'])){
                $post_data['LEAD_UF_CRM_59BADE3DB1A12'] = $k;
                break;
            }
        }
    }
  
    // Поверхность букв:
    if(!empty($post['fld_texture'])){
        foreach ($gloss_ARR as $k=>$v) {
            $mask = preg_quote(substr($v, 0, -2), '/'); 
            if(preg_match("/($mask)/imu", $post['fld_texture'])){
                $post_data['LEAD_UF_CRM_59BADE3DBB8F1'] = $k;
                break;
            }
        }
    }

    // Цвет LEAD_UF_CRM_59BADE3DC43B3
    if(!empty($post['fld_color'])){
        foreach ($color_ARR as $k=>$v) {
            $mask = preg_quote($v, '/');
            if(preg_match("/^[\s\S]*($mask)[\s\S]*$/imu", $post['fld_color'])){
                $post_data['LEAD_UF_CRM_59BADE3DC43B3'] = $k;
                break;
            }
        }
    }
  
    if(!empty($post['fld_size'])){
        foreach ($size_ARR as $k=>$v) {
            $mask = preg_quote(substr($v, 0, -2), '/');
            if(preg_match("/^[\s\S]*($mask)[\s\S]*$/imu", $post['fld_size'])){
                $post_data['LEAD_UF_CRM_59BAE7C86E03D'] = $k;
                break;
            }
        }
    }
  
    // if(!empty($post['upload_file'])){
    // 	$post_data['LEAD_UF_CRM_1505375747'] = curl_file_create($post['upload_file']);
    // 	// "@".$post['upload_file'].'; filename="'.basename($post['upload_file']).'";';
    // }
  
  
    $curl = curl_init(); //инициализация сеанса
    curl_setopt($curl, CURLOPT_URL, $CRM_FORM_URL . http_build_query($formID));
    curl_setopt($curl, CURLOPT_HEADER, false); // не выводим заголовки
    curl_setopt($curl, CURLOPT_POST, 1); //передача данных методом POST
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //теперь curl вернет нам ответ, а не выведет
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); //тут переменные которые будут переданы методом POST    
    curl_setopt($curl, CURLOPT_ENCODING, '');
    curl_setopt($curl, CURLOPT_USERAGENT, 'Chrome/54.0.2840.99');

    $res = curl_exec($curl);

    //если ошибка то номер и сообщение сохраняем в логи
    if(!$res) {
        $error = curl_error($curl).'('.curl_errno($curl).')';
        __writeToLog($error, 'ERROR', __DIR__ . '/__errors.txt');
    }
    else {
        __writeToLog($res, 'CRM RESULT',__DIR__ . '/__bx24_.txt');
    }
    curl_close($curl);
    return $res;
}