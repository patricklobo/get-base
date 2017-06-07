<?php

namespace getbasefutebol;

class Validador
{

    public static function get_html($url)
    {

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your Application Name');
        $query = curl_exec($curl_handle);
        curl_close($curl_handle);
        return $query;
    }


    public static function remove_acentos($string)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $string);
    }


    public static function get_class_dom($elemento)
    {
        $classes_str = $elemento->getAttribute("class");
        $classes = explode(" ", $classes_str);
        return $classes;
    }


    public static function get_atributo_valor_dom($elemento)
    {
        $lista = [];
        $atributos = $elemento->attributes;
        foreach ($atributos as $atributo) {
            $lista[$atributo->name] = $atributo->value;
        }
        return $lista;
    }


    public static function data_brasil_to_us($data)
    {
        return implode("-", array_reverse(explode("/", $data)));
    }


    public static function porcentagem($porcentagem, $total)
    {
        return ($porcentagem / 100) * $total;
    }



}