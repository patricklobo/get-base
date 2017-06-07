<?php
/**
 * Created by PhpStorm.
 * User: jordanviana
 * Date: 07/06/17
 * Time: 15:39
 */

namespace getbasefutebol;

require_once "Validador.php";
class Integracao
{
    public static function get_jogos_sportingbet($percent)
    {
        $url_base = "https://br.sportingbet.com/esportes-futebol/0-102.html";
        $html1 = Validador::get_html($url_base);
        $dom = new \DOMDocument;
        $dom->loadHTML(Validador::remove_acentos($html1));
        $divs = $dom->getElementsByTagName("div");
        $campeonatos = [];
        foreach ($divs as $div) {
            $classes = Validador::get_class_dom($div);
            foreach ($classes as $classe) {
                if ($classe == "box") {
                    $box_nomes = $div->getElementsByTagName("h2");
                    foreach ($box_nomes as $box_nome) {
                        if ($box_nome->nodeValue != "Ao Vivo" && $box_nome->nodeValue != "Vencedor" && $box_nome->nodeValue != "In Play Matches - Wednesday" && $box_nome->nodeValue != "In Play Matches - Thursday" && $box_nome->nodeValue != "In Play Matches - Friday") {
                            $divs2 = $div->getElementsByTagName("div");
                            foreach ($divs2 as $div2) {
                                $div2_classes = Validador::get_class_dom($div2);
                                foreach ($div2_classes as $div2_classe) {
                                    if ($div2_classe == "dd") {
                                        $as = $div2->getElementsByTagName("a");
                                        foreach ($as as $a) {
                                            $campeonato = new \stdClass();
                                            $campeonato->campeonato = $a->nodeValue;
                                            $campeonato->jogos = [];
                                            $a_atributos = Validador::get_atributo_valor_dom($a);
                                            $id_camp = explode(".", explode("1-102-", $a_atributos["href"])[1])[0];
                                            $html_jogos = Validador::get_html("https://br.sportingbet.com/services/CouponTemplate.mvc/GetCoupon?couponAction=EVENTCLASSCOUPON&sportIds=102&marketTypeId=&eventId=&bookId=&eventClassId=$id_camp&sportId=102&eventTimeGroup=");
                                            $dom2 = new \DOMDocument;
                                            $dom2->loadHTML(Validador::remove_acentos($html_jogos));
                                            $divs_2 = $dom2->getElementsByTagName("div");
                                            foreach ($divs_2 as $div_2) {
                                                $classe_div_3 = $div_2->getAttribute("class");
                                                if ($classe_div_3 == "event active") {
                                                    $jogo = new \stdClass();
                                                    $atributos_div3 = Validador::get_atributo_valor_dom($div_2);
                                                    $id_jogo = explode("_", $atributos_div3["id"])[1];
                                                    $jogo->CD_JOGO = $id_jogo;
                                                    $divs_jogo = $div_2->getElementsByTagName("div");
                                                    foreach ($divs_jogo as $div_jogo) {
                                                        $classe_div_jogo = $div_jogo->getAttribute("class");
                                                        if ($classe_div_jogo == "columns") {
                                                            $colum_divs = $div_jogo->getElementsByTagName("div");
                                                            foreach ($colum_divs as $colum_div) {
                                                                $classe_colum_div = $colum_div->getAttribute("class");
                                                                if ($classe_colum_div == "eventInfo") {
                                                                    $as = $div_jogo->getElementsByTagName("a");
                                                                    foreach ($as as $a) {
                                                                        $classe_a = $a->getAttribute("class");
                                                                        if ($classe_a == "eventNameLink") {
                                                                            $home = explode(" x ", $a->nodeValue)[0];
                                                                            $visi = explode(" x ", $a->nodeValue)[1];
                                                                            $jogo->home = $home;
                                                                            $jogo->visi = $visi;
                                                                        }
                                                                    }
                                                                    $spans = $div_jogo->getElementsByTagName("span");
                                                                    foreach ($spans as $span) {
                                                                        $classe_span = $span->getAttribute("class");
                                                                        if ($classe_span == "StartTime") {
                                                                            $jogo->DATA_JOGO = Validador::data_brasil_to_us(explode(" ", $span->nodeValue)[0]);
                                                                            $jogo->HORA_JOGO = explode(" ", $span->nodeValue)[1];
                                                                        }
                                                                    }
                                                                }
                                                                if ($classe_colum_div == "odds home active") {
                                                                    $spans_home = $colum_div->getElementsByTagName("span");
                                                                    foreach ($spans_home as $span_home) {
                                                                        $classe_span_home = $span_home->getAttribute("class");
                                                                        if ($classe_span_home == "priceText wide  EU") {
                                                                            $jogo->CASA = $span_home->nodeValue;
                                                                            $jogo->CASA = ($jogo->CASA - Validador::porcentagem($percent, $jogo->CASA));
                                                                        }
                                                                    }
                                                                }
                                                                if ($classe_colum_div == "odds draw active") {
                                                                    $spans_home = $colum_div->getElementsByTagName("span");
                                                                    foreach ($spans_home as $span_home) {
                                                                        $classe_span_home = $span_home->getAttribute("class");
                                                                        if ($classe_span_home == "priceText wide  EU") {
                                                                            $jogo->EMPATE = $span_home->nodeValue;
                                                                            $jogo->EMPATE = ($jogo->EMPATE - Validador::porcentagem($percent, $jogo->EMPATE));
                                                                        }
                                                                    }
                                                                }
                                                                if ($classe_colum_div == "odds away active") {
                                                                    $spans_home = $colum_div->getElementsByTagName("span");
                                                                    foreach ($spans_home as $span_home) {
                                                                        $classe_span_home = $span_home->getAttribute("class");
                                                                        if ($classe_span_home == "priceText wide  EU") {
                                                                            $jogo->FORA = $span_home->nodeValue;
                                                                            $jogo->FORA = ($jogo->FORA - Validador::porcentagem($percent, $jogo->FORA));
                                                                        }
                                                                    }
                                                                }
                                                                if ($jogo->CASA < $jogo->FORA) {
                                                                    $gol_meio = $jogo->CASA * 1.48;
                                                                    $dupla = ($jogo->FORA / 2) * 0.93;
                                                                    $gol_meio_casa = $jogo->CASA * 1.25;
                                                                    $gol_meio_fora = $jogo->FORA * 1.25;
                                                                    $amb = 1.20;
                                                                } else {
                                                                    $gol_meio = $jogo->FORA * 1.48;
                                                                    $dupla = ($jogo->CASA / 2) * 0.93;
                                                                    $gol_meio_casa = $jogo->CASA * 1.25;
                                                                    $gol_meio_fora = $jogo->FORA * 1.25;
                                                                    $amb = 1.20;
                                                                }
                                                            }
                                                            $jogo->extra = new \stdClass();
                                                            $jogo->extra->CASA = new \stdClass();
                                                            $jogo->extra->CASA->id = 1;
                                                            $jogo->extra->CASA->valor = $jogo->CASA;

                                                            $jogo->extra->EMPATE = new \stdClass();
                                                            $jogo->extra->EMPATE->id = 2;
                                                            $jogo->extra->EMPATE->valor = $jogo->EMPATE;

                                                            $jogo->extra->FORA = new \stdClass();
                                                            $jogo->extra->FORA->id = 3;
                                                            $jogo->extra->FORA->valor = $jogo->FORA;

                                                            $jogo->extra->GOL_MEIO = new \stdClass();
                                                            $jogo->extra->GOL_MEIO->id = 4;
                                                            $jogo->extra->GOL_MEIO->valor = $gol_meio;

                                                            $jogo->extra->DUPLA_CHANCE = new \stdClass();
                                                            $jogo->extra->DUPLA_CHANCE->id = 5;
                                                            $jogo->extra->DUPLA_CHANCE->valor = $dupla;

                                                            $jogo->extra->GOL_MEIO_CASA = new \stdClass();
                                                            $jogo->extra->GOL_MEIO_CASA->id = 6;
                                                            $jogo->extra->GOL_MEIO_CASA->valor = $gol_meio_casa;

                                                            $jogo->extra->GOL_MEIO_FORA = new \stdClass();
                                                            $jogo->extra->GOL_MEIO_FORA->id = 7;
                                                            $jogo->extra->GOL_MEIO_FORA->valor = $gol_meio_fora;

                                                            $jogo->extra->AMB = new \stdClass();
                                                            $jogo->extra->AMB->id = 8;
                                                            $jogo->extra->AMB->valor = $amb;

                                                            $jogo->extra->NAMB = new \stdClass();
                                                            $jogo->extra->NAMB->id = 9;
                                                            $jogo->extra->NAMB->valor = 1.50;

                                                            $jogo->extra->RE0X0 = new \stdClass();
                                                            $jogo->extra->RE0X0->id = 10;
                                                            $jogo->extra->RE0X0->valor = $jogo->extra->NAMB->valor;

                                                            $jogo->extra->RE0X1 = new \stdClass();
                                                            $jogo->extra->RE0X1->id = 11;
                                                            $jogo->extra->RE0X1->valor = $jogo->extra->FORA->valor * 2.2;

                                                            $jogo->extra->RE0X2 = new \stdClass();
                                                            $jogo->extra->RE0X2->id = 12;
                                                            $jogo->extra->RE0X2->valor = $jogo->extra->FORA->valor * 2.3;

                                                            $jogo->extra->RE0X3 = new \stdClass();
                                                            $jogo->extra->RE0X3->id = 13;
                                                            $jogo->extra->RE0X3->valor = $jogo->extra->FORA->valor * 2.5;

                                                            $jogo->extra->RE0X4 = new \stdClass();
                                                            $jogo->extra->RE0X4->id = 14;
                                                            $jogo->extra->RE0X4->valor = $jogo->extra->FORA->valor * 2.6;

                                                            $jogo->extra->RE0X5 = new \stdClass();
                                                            $jogo->extra->RE0X5->id = 15;
                                                            $jogo->extra->RE0X5->valor = $jogo->extra->FORA->valor * 2.8;

                                                            $jogo->extra->RE1X0 = new \stdClass();
                                                            $jogo->extra->RE1X0->id = 16;
                                                            $jogo->extra->RE1X0->valor = $jogo->extra->CASA->valor * 2.2;

                                                            $jogo->extra->RE1X1 = new \stdClass();
                                                            $jogo->extra->RE1X1->id = 17;
                                                            $jogo->extra->RE1X1->valor = $jogo->extra->EMPATE->valor * 1.9;

                                                            $jogo->extra->RE1X2 = new \stdClass();
                                                            $jogo->extra->RE1X2->id = 18;
                                                            $jogo->extra->RE1X2->valor = $jogo->extra->FORA->valor * 2.3;

                                                            $jogo->extra->RE1X3 = new \stdClass();
                                                            $jogo->extra->RE1X3->id = 19;
                                                            $jogo->extra->RE1X3->valor = $jogo->extra->FORA->valor * 2.5;

                                                            $jogo->extra->RE1X4 = new \stdClass();
                                                            $jogo->extra->RE1X4->id = 20;
                                                            $jogo->extra->RE1X4->valor = $jogo->extra->FORA->valor * 2.6;

                                                            $jogo->extra->RE1X5 = new \stdClass();
                                                            $jogo->extra->RE1X5->id = 21;
                                                            $jogo->extra->RE1X5->valor = $jogo->extra->FORA->valor * 2.8;

                                                            $jogo->extra->RE2X0 = new \stdClass();
                                                            $jogo->extra->RE2X0->id = 22;
                                                            $jogo->extra->RE2X0->valor = $jogo->extra->CASA->valor * 2.2;

                                                            $jogo->extra->RE2X1 = new \stdClass();
                                                            $jogo->extra->RE2X1->id = 23;
                                                            $jogo->extra->RE2X1->valor = $jogo->extra->CASA->valor * 2.3;

                                                            $jogo->extra->RE2X2 = new \stdClass();
                                                            $jogo->extra->RE2X2->id = 24;
                                                            $jogo->extra->RE2X2->valor = $jogo->extra->EMPATE->valor * 2;

                                                            $jogo->extra->RE2X3 = new \stdClass();
                                                            $jogo->extra->RE2X3->id = 25;
                                                            $jogo->extra->RE2X3->valor = $jogo->extra->FORA->valor * 2.5;

                                                            $jogo->extra->RE2X4 = new \stdClass();
                                                            $jogo->extra->RE2X4->id = 26;
                                                            $jogo->extra->RE2X4->valor = $jogo->extra->FORA->valor * 2.6;

                                                            $jogo->extra->RE2X5 = new \stdClass();
                                                            $jogo->extra->RE2X5->id = 27;
                                                            $jogo->extra->RE2X5->valor = $jogo->extra->FORA->valor * 2.8;

                                                            $jogo->extra->RE3X0 = new \stdClass();
                                                            $jogo->extra->RE3X0->id = 28;
                                                            $jogo->extra->RE3X0->valor = $jogo->extra->CASA->valor * 2.2;

                                                            $jogo->extra->RE3X1 = new \stdClass();
                                                            $jogo->extra->RE3X1->id = 29;
                                                            $jogo->extra->RE3X1->valor = $jogo->extra->CASA->valor * 2.3;

                                                            $jogo->extra->RE3X2 = new \stdClass();
                                                            $jogo->extra->RE3X2->id = 30;
                                                            $jogo->extra->RE3X2->valor = $jogo->extra->CASA->valor * 2.4;

                                                            $jogo->extra->RE3X3 = new \stdClass();
                                                            $jogo->extra->RE3X3->id = 31;
                                                            $jogo->extra->RE3X3->valor = $jogo->extra->EMPATE->valor * 2.2;

                                                            $jogo->extra->RE3X4 = new \stdClass();
                                                            $jogo->extra->RE3X4->id = 32;
                                                            $jogo->extra->RE3X4->valor = $jogo->extra->FORA->valor * 2.6;

                                                            $jogo->extra->RE3X5 = new \stdClass();
                                                            $jogo->extra->RE3X5->id = 33;
                                                            $jogo->extra->RE3X5->valor = $jogo->extra->FORA->valor * 2.8;

                                                            $jogo->extra->RE4X0 = new \stdClass();
                                                            $jogo->extra->RE4X0->id = 34;
                                                            $jogo->extra->RE4X0->valor = $jogo->extra->CASA->valor * 2.2;

                                                            $jogo->extra->RE4X1 = new \stdClass();
                                                            $jogo->extra->RE4X1->id = 35;
                                                            $jogo->extra->RE4X1->valor = $jogo->extra->CASA->valor * 2.3;

                                                            $jogo->extra->RE4X2 = new \stdClass();
                                                            $jogo->extra->RE4X2->id = 36;
                                                            $jogo->extra->RE4X2->valor = $jogo->extra->CASA->valor * 2.4;

                                                            $jogo->extra->RE4X3 = new \stdClass();
                                                            $jogo->extra->RE4X3->id = 37;
                                                            $jogo->extra->RE4X3->valor = $jogo->extra->CASA->valor * 2.5;

                                                            $jogo->extra->RE4X4 = new \stdClass();
                                                            $jogo->extra->RE4X4->id = 38;
                                                            $jogo->extra->RE4X4->valor = $jogo->extra->EMPATE->valor * 2.4;

                                                            $jogo->extra->RE5X0 = new \stdClass();
                                                            $jogo->extra->RE5X0->id = 39;
                                                            $jogo->extra->RE5X0->valor = $jogo->extra->CASA->valor * 2.2;

                                                            $jogo->extra->RE5X1 = new \stdClass();
                                                            $jogo->extra->RE5X1->id = 40;
                                                            $jogo->extra->RE5X1->valor = $jogo->extra->CASA->valor * 2.3;

                                                            $jogo->extra->RE5X2 = new \stdClass();
                                                            $jogo->extra->RE5X2->id = 41;
                                                            $jogo->extra->RE5X2->valor = $jogo->extra->CASA->valor * 2.4;

                                                            $jogo->extra->RE5X3 = new \stdClass();
                                                            $jogo->extra->RE5X3->id = 42;
                                                            $jogo->extra->RE5X3->valor = $jogo->extra->CASA->valor * 2.5;

                                                            foreach ($jogo->extra as $key => $value) {
                                                                if ($value->valor < 1) {
                                                                    $value->valor = 1.1;
                                                                }
                                                                if ($value->valor > 8) {
                                                                    $value->valor = 8;
                                                                }
                                                                $value->valor = round($value->valor, 2);
                                                            }
                                                        }
                                                    }
                                                    if (!$jogo->HORA_JOGO)
                                                        continue;
                                                    if ($jogo->home) {
                                                        if (!$campeonato->jogos[$jogo->DATA_JOGO])
                                                            $campeonato->jogos[$jogo->DATA_JOGO] = new \stdClass();
                                                        if (!$campeonato->jogos[$jogo->DATA_JOGO]->JOGOS)
                                                            $campeonato->jogos[$jogo->DATA_JOGO]->JOGOS = [];
                                                        $campeonato->jogos[$jogo->DATA_JOGO]->JOGOS[] = $jogo;
                                                    }
                                                }
                                            }

                                            $campeonatos[] = $campeonato;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $campeonatos;
    }
}