<?php 
namespace app\index\controller;

use think\Cache;
use think\Config;
use think\Exception;
use think\Log;
use think\Session;
use think\Db;
class BaseService
{
    private $configData = null;
    private $ZIYUAN_URL = null;
    private $switchData = null;
    private $systemData = null;
    private $requestHost = null;
    private $nodeHost = null;
    const HUANDENGPIAN_URL = "https://v.qq.com";
    const DY_URL = "http://www.360kan.com/dianying/listajax?rank=rankhot&cat=all&year=all&area=all&act=all&pageno=1";
    const DYZUIXIN_URL = "http://www.360kan.com/dianying/list?cat=all&year=all&area=all&act=all&rank=createtime";
    const DYZUIXINDSJ_URL = "http://www.360kan.com/dianshi/list?cat=all&year=all&area=all&act=all&rank=rankhot";
    const DYZUIXINZY_URL = "http://www.360kan.com/zongyi/list?cat=all&act=all&area=all&rank=rankhot";
    const DYZUIXINDM_URL = "http://www.360kan.com/dongman/list?cat=100&year=all&area=all&rank=rankhot";
    const MVLIST_URL = "http://www.yinyuetai.com/mv/get-playlist?";
    const YY_SHNEQU_URL = "http://www.yy.com/shenqu/global/leftmenu.do?";
    const DY_LIST_URL = "https://www.360kan.com/dianying/listajax?";
    public function __construct()
    {
        ini_set("user_agent", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;)");
        $this->requestHost = base64_encode($_SERVER["HTTP_HOST"] . "|" . $_SERVER["SERVER_NAME"]);
        $this->ZIYUAN_URL = $this->getZiyunUrl();
        $system = Db::table("system")->where("id", 1)->find();
        $this->systemData = $system;
        if($system['node_host']==1){
            $this->nodeHost='http://diso.me:93';
        }else{
            $this->nodeHost='http://bejson.cc';
        }
        $a = $this->authCheck();
        if ($a == 2) {
            echo "<script> if(confirm('乐橙CMS提示：请勿使用盗版，支持正版。去授权？')){window.location.href='https://jq.qq.com/?_wv=1027&k=5jRdJtf'};</script>";
        } elseif ($a == 3) {
            echo "<script> if(confirm('乐橙CMS提示：请先授权，去授权？')){window.location.href='https://jq.qq.com/?_wv=1027&k=5jRdJtf'};</script>";
        }
    }
    public function authNew()
    {
        if (!cache("authtext")) {
            $auth = file_get_contents(base64_decode("aHR0cDovL2JlanNvbi5jYy9hcGkvYXV0aC9pbmRleD9kb21hbj0=") . $_SERVER["HTTP_HOST"]);
            $auth = json_decode($auth, true);
            if ($auth["status"] == 1) {
                cache("authtext", $auth["authTxt"], 5 * 24 * 3600);
                return 1;
            } else {
                return 3;
            }
        } else {
            return 1;
        }
    }
    public function authCheck()
    {
        $st = "/" . "a" . "u" . "t" . "h" . ".";
        $str = "h" . "t" . "t" . "p" . "s" . ":" . "/";
        $str4 = "u" . "r" . "l" . "=";
        $str1 = "j" . "c" . "w" . "l" . "e" . "." . "c" . "o" . "m";
        $str2 = "/" . "a" . "p" . "i" . "s" . "/" . "l";
        $str3 = "e" . "c" . "m" . "s" . "." . "p" . "h" . "p" . "?";
        if (!cache("authCode")) {
            $auth = file_get_contents($str . $st . $str1 . $str2 . $str3 . $str4 . $_SERVER["HTTP_HOST"] . "&authcode=");
            $auth = json_decode($auth, true);
            if ($auth["code"] == 1) {
                cache("authCode", $auth["authcode"], 5 * 24 * 3600);
                return 1;
            } else {
                return 3;
            }
        } else {
            return 1;
        }
    }
    public function curlData($url, $param = '')
    {
        $useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
        $header = array("Accept-Language: zh-cn", "Connection: Keep-Alive", "Cache-Control: no-cache");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        $result = curl_exec($ch);
        return $result;
    }
    public function getZiyunUrl()
    {
        return "http://" . config("vod_config.vod_zy_list");
    }
    public function getUrlFileExt($url)
    {
        $ary = parse_url($url);
        $file = basename($ary["path"]);
        $ext = explode(".", $file);
        return $ext[1];
    }
    public function get360dy()
    {
        $info = file_get_contents(self::DY_URL);
        return json_decode($info, true);
    }
    public function rrd($url)
    {
        ini_set("user_agent", "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;)");
        set_time_limit(0);
        $api = "http://api.rrd.me/api.php?format=json&url=" . $url . "&apikey=MfbFW1I92LBiQs7Js1@ddd";
        $data = file_get_contents($api);
        $data = json_decode($data, true);
        return $data;
    }
    public function getHotDy()
    {
        $ver = file_get_contents($this->nodeHost."/api/index/hotDy?rehost=" . $this->requestHost);
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function getHuanDeng()
    {
        $ver = file_get_contents($this->nodeHost."/api/index/getHUangdeng?rehost=" . $this->requestHost);
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function getPaiHang()
    {
        $ver = file_get_contents($this->nodeHost."/api/api/getPaiHang?rehost=" . $this->requestHost);
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function get360zuixindy()
    {
        $urlGet = $this->nodeHost."/api/index/get360dyauth?rehost=" . $this->requestHost;
        $info = $this->curlData($urlGet, array("host" => self::DYZUIXIN_URL));
        $infodsj = $this->curlData($urlGet, array("host" => self::DYZUIXINDSJ_URL));
        $infozy = $this->curlData($urlGet, array("host" => self::DYZUIXINZY_URL));
        $infodm = $this->curlData($urlGet, array("host" => self::DYZUIXINDM_URL));
        return ["zxdy" => json_decode($info, true), "zxdsj" => json_decode($infodsj, true), "zxzy" => json_decode($infozy, true), "zxdm" => json_decode($infodm, true)];
    }
    public function get360playurl($urlorId)
    {
        $damin = "http://www.360kan.com/" . $urlorId;
        $tvinfo = file_get_contents($damin);
        $bflist = "#<a data-daochu=(.*?) class=\"btn js-site ea-site (.*?)\" href=\"(.*?)\">(.*?)</a>#";
        $biaoti = "#<h1>(.*?)</h1>#";
        $tvzz = "#<div (class=\"site-wrap\")?\\s*id=\"js-site-wrap\">[\\s\\S]+?(class=\"num-tab-main\\s*g-clear\\s*js-tab\")?\\s*(style=\"display:none;\")?>[\\s\\S]+?<a data-num=\"(.*?)\"\\s*data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?</div>#";
        $tvzz1 = "#<a data-num=\"(.*?)\"\\s*data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
        $dyzz = "#<span class=\"txt\">站点排序 ：</span>[\\s\\S]+?<div style=' visibility:hidden'#";
        $bflist = "#<a data-daochu=(.*?) class=\"btn js-site ea-site (.*?)\" href=\"(.*?)\">(.*?)</a>#";
        $jianjie = "#style=\"display:none;\"><span>简介[\\s\\S]+?js-close btn#";
        $jianjie1 = "#</span>(.*?)<a href#";
        $biaoti = "#<h1>(.*?)</h1>#";
        preg_match_all($biaoti, $tvinfo, $btarr);
        preg_match_all($bflist, $tvinfo, $tvlist);
        $dyName = $btarr[1][0];
        $playData = [];
        foreach ($tvlist[3] as $key => $value) {
            foreach ($tvlist[4] as $k => $v) {
                $playData[$key] = ["urlData" => [1 => $value], "playType" => $tvlist[4][$key]];
            }
        }
        $other = $this->getSearchCx($btarr[1][0]);
        return ["dyName" => $dyName, "other" => $other["data"][0], "playData" => $playData];
    }
    public function getcxdy($page, $cid = '')
    {
        $systemDataUse = $this->systemData;
        $zyUrl = "http://" . $systemDataUse["vod_zy_list"];
        $page = $page ? $page : 1;
        if ($cid != '') {
            $apiUrl = $zyUrl . "?p=" . $page . "&cid=" . $cid;
        } else {
            $apiUrl = $zyUrl . "?p=" . $page;
        }
        @($jsonData = file_get_contents($apiUrl));
        return json_decode($jsonData, true);
    }
    public function getcxdyv10($page, $cid)
    {
        $systemDataUse = $this->systemData;
        $zyUrl = "http://" . $systemDataUse["vod_zy_macv10"];
        $page = $page ? $page : 1;
        @($type = file_get_contents($zyUrl));
        $type = json_decode($type, true);
        $data["type"] = $type["class"];
        if ($cid != '') {
            $apiUrl = $zyUrl . "ac/detail/pg/" . $page . "/t/" . $cid;
        } else {
            $apiUrl = $zyUrl . "ac/detail/pg/" . $page;
        }
        @($getData = json_decode(file_get_contents($apiUrl), true));
        $data["data"] = $getData;
        return $data;
    }
    public function getMvListData($size = 6)
    {
        $info = file_get_contents(self::MVLIST_URL . "size=" . $size);
        return json_decode($info, true);
    }
    public function searchData($wd)
    {
        $systemDataUse = $this->systemData;
        $zyData = [];
        @($quanData = file_get_contents($this->nodeHost."/api/api/getSearchData?wd=" . urlencode($wd) . "&rehost=" . $this->requestHost));
        $zyArr = $systemDataUse["vod_yun_config"];
        $a = explode("%%%", $zyArr);
        foreach ($a as $k => $v) {
            $b[$k] = explode("%", $v);
        }
        $flag = 0;
        foreach ($b as $k9 => $v9) {
            @($zyso = file_get_contents($v9[1] . "?wd=" . $wd));
            $data = json_decode($zyso, true);
            if (isset($data["data"])) {
                foreach ($data["data"] as $kk => &$vv) {
                    $vv["vod_id"] = "/cx/" . $vv["vod_id"] . "/src/" . $k9;
                    $vv["vod_name"] = $vv["vod_name"] . "（资源--" . $v9[0] . ")";
                    $zyData[$flag] = $vv;
                    $flag++;
                }
            }
        }
        return ["quanData" => json_decode($quanData, true), "zyData" => $zyData, "wd" => $wd];
    }
    public function searchDatalist($wd)
    {
        $systemDataUse = $this->systemData;
        $zyData = [];
        @($quanData = file_get_contents($this->nodeHost."/api/api/getSearchData?wd=" . urlencode($wd) . "&rehost=" . $this->requestHost));
        $zyArr = $systemDataUse["vod_yun_config"];
        $a = explode("%%%", $zyArr);
        foreach ($a as $k => $v) {
            $b[$k] = explode("%", $v);
        }
        $flag = 0;
        foreach ($b as $k9 => $v9) {
            @($zyso = file_get_contents($v9[1] . "?wd=" . $wd));
            $data = json_decode($zyso, true);
            if (isset($data["data"])) {
                foreach ($data["data"] as $kk => &$vv) {
                    $vv["vod_id"] = "/cx/" . $vv["vod_id"] . "/src/" . $k9;
                    $vv["vod_name"] = $vv["vod_name"];
                    $vv["source"] = "(资源--" . $v9[0] . ")";
                    $zyData[$flag] = $vv;
                    $flag++;
                }
            }
        }
        return ["quanData" => json_decode($quanData, true), "zyData" => $zyData, "wd" => $wd];
    }
    public function searchDataNew($wd)
    {
        $systemDataUse = $this->systemData;
        @($quanData = file_get_contents($this->nodeHost."/api/api/getSearchData?wd=". urlencode($wd) . "&rehost=" . $this->requestHost));
        @($zyso = file_get_contents('' . "http://" . $systemDataUse["vod_zy_list"] . "?wd=" . $wd . ''));
        $data = json_decode($zyso, true);
        if ($data) {
            foreach ($data["data"] as $kk => &$vv) {
                $vv["vod_id"] = "/cx/" . $vv["vod_id"];
            }
        }
        return ["quanData" => json_decode($quanData, true), "zyData" => isset($data["data"]) ? $data["data"] : [], "wd" => $wd];
    }
    public function seachDataXML($wd)
    {
        $systemDataUse = $this->systemData;
        $zyData = [];
        @($quanData = file_get_contents($this->nodeHost."/api/api/getSearchData?wd=". urlencode($wd) . "&rehost=" . $this->requestHost));
        $zyArr = $systemDataUse["vod_yun_config"];
        $a = explode("%%%", $zyArr);
        foreach ($a as $k => $v) {
            $b[$k] = explode("%", $v);
        }
        $flag = 0;
        foreach ($b as $k9 => $v9) {
            @($zyso = file_get_contents($v9[1] . "?ac=videolist&wd=" . $wd));
            $data = json_decode($zyso, true);
            if (isset($data["data"])) {
                foreach ($data["data"] as $kk => &$vv) {
                    $vv["vod_id"] = "/cx/" . $vv["vod_id"] . "/src/" . $k9;
                    $vv["vod_name"] = $vv["vod_name"] . "（资源--" . $v9[0] . ")";
                    $zyData[$flag] = $vv;
                    $flag++;
                }
            }
        }
        return ["quanData" => json_decode($quanData, true), "zyData" => $zyData, "wd" => $wd];
    }
    public function searchDatav10($wd)
    {
        $systemDataUse = $this->systemData;
        $zyData = [];
        @($quanData = file_get_contents($this->nodeHost."/api/api/getSearchData?wd=". urlencode($wd) . "&rehost=" . $this->requestHost));
        $zyArr = $systemDataUse["vod_yun_macv10_list"];
        $a = explode("%%%", $zyArr);
        foreach ($a as $k => $v) {
            $b[$k] = explode("%", $v);
        }
        $flag = 0;
        foreach ($b as $k9 => $v9) {
            @($zyso = file_get_contents($v9[1] . "/ac/detail/wd/" . $wd));
            $data = json_decode($zyso, true);
            if (isset($data["list"])) {
                foreach ($data["list"] as $kk => &$vv) {
                    $vv["vod_id"] = "/cx/" . $vv["vod_id"] . "/src/" . $k9;
                    $vv["vod_name"] = $vv["vod_name"] . "（来源--" . $v9[0] . ")";
                    $vv["list_name"] = $vv["type_name"];
                    $vv["vod_addtime"] = $vv["vod_time"];
                    $zyData[$flag] = $vv;
                    $flag++;
                }
            }
        }
        return ["quanData" => json_decode($quanData, true), "zyData" => $zyData, "wd" => $wd];
    }
    public function getSearchCx($wd)
    {
        $zyso = file_get_contents($this->ZIYUAN_URL . "?wd=" . $wd . '');
        $data = json_decode($zyso, true);
        return $data;
    }
    public function getYunData($wd)
    {
        $systemDataUse = $this->systemData;
        $zyUrl = $systemDataUse["vod_yun_config"];
        $urlData = explode("%%%", $zyUrl);
        foreach ($urlData as $ks => $vs) {
            $u1[] = explode("%", $vs);
        }
        foreach ($u1 as $kq => $vq) {
            $umast[$vq[0]] = $vq[1];
        }
        $mastPlayData = [];
        $mastPlayData["playData"] = [];
        $mastPlayData["dyName"] = $wd;
        foreach ($umast as $kk => $vb) {
            @($zyYun = file_get_contents($vb . "?wd=" . $wd));
            $data = json_decode($zyYun, true);
            if (isset($data["data"][0])) {
                $mastPlayData["other"] = $data["data"][0];
                if (strpos($data["data"][0]["vod_url"], ".m3u8")) {
                    $playUrlData = explode("\$\$\$", $data["data"][0]["vod_url"]);
                    foreach ($playUrlData as $k1 => $v1) {
                        if (strpos($v1, ".m3u8")) {
                            $url1 = str_replace("\r\n", '', $v1);
                        }
                    }
                    $url2 = explode(".m3u8", $url1);
                    foreach ($url2 as $k2 => &$v2) {
                        if ($v2 != '') {
                            $v3 = $v2;
                            $v2 = trim(strrchr($v2, "\$"), "\$") . ".m3u8";
                            $url4[$k2]["lianjie"] = $v2;
                            $chai = explode("\$", $v3);
                            $url3[$k2]["url"] = $chai[1] . ".m3u8";
                            $url3[$k2]["qishu"] = $chai[0];
                        } else {
                            unset($url2[$k2]);
                        }
                    }
                    array_push($mastPlayData["playData"], ["playType" => $kk, "urlData" => $url4, "zongyi" => $url3]);
                }
            }
        }
        return $mastPlayData;
    }
    public function xml_search_vod($param, $html = '')
    {
        $html = mac_curl_get($param["cjurl"]);
        if (empty($html)) {
            return ["code" => 1001, "msg" => "连接API资源库失败，通常为服务器网络不稳定或禁用了采集"];
        }
        $xml = @simplexml_load_string($html);
        $key = 0;
        $array_data = [];
        if (!$xml->list->video) {
            return ["code" => 0, "msg" => "搜索被禁用"];
        }
        foreach ($xml->list->video as $video) {
            $array_data[$key]["vod_id"] = (string) $video->id;
            $array_data[$key]["vod_name"] = (string) $video->name;
            $array_data[$key]["vod_remarks"] = (string) $video->note;
            $array_data[$key]["type_name"] = (string) $video->type;
            $array_data[$key]["vod_pic"] = (string) $video->pic;
            $array_data[$key]["vod_lang"] = (string) $video->lang;
            $array_data[$key]["vod_area"] = (string) $video->area;
            $array_data[$key]["vod_year"] = (string) $video->year;
            $array_data[$key]["vod_serial"] = (string) $video->state;
            $array_data[$key]["vod_actor"] = (string) $video->actor;
            $array_data[$key]["vod_director"] = (string) $video->director;
            $array_data[$key]["vod_content"] = (string) $video->des;
            $array_data[$key]["vod_status"] = 1;
            $array_data[$key]["vod_type"] = $array_data[$key]["list_name"];
            $array_data[$key]["vod_time"] = (string) $video->last;
            $array_data[$key]["vod_total"] = 0;
            $array_data[$key]["vod_isend"] = 1;
            if ($array_data[$key]["vod_serial"]) {
                $array_data[$key]["vod_isend"] = 0;
            }
            $array_from = [];
            $array_url = [];
            $array_server = [];
            $array_note = [];
            if ($count = count($video->dl->dd)) {
                $i = 0;
                while ($i < $count) {
                    $array_from[$i] = (string) $video->dl->dd[$i]["flag"];
                    $array_url[$i] = $this->vod_xml_replace((string) $video->dl->dd[$i]);
                    $array_server[$i] = "no";
                    $array_note[$i] = '';
                    $i++;
                }
            } else {
                $array_from[] = (string) $video->dt;
                $array_url[] = '';
                $array_server[] = '';
                $array_note[] = '';
            }
            if (strpos(base64_decode($param["param"]), "ct=1") !== false) {
                $array_data[$key]["vod_down_from"] = implode("\$\$\$", $array_from);
                $array_data[$key]["vod_down_url"] = implode("\$\$\$", $array_url);
                $array_data[$key]["vod_down_server"] = implode("\$\$\$", $array_server);
                $array_data[$key]["vod_down_note"] = implode("\$\$\$", $array_note);
            } else {
                $array_data[$key]["vod_play_from"] = implode("\$\$\$", $array_from);
                $array_data[$key]["vod_play_url"] = implode("\$\$\$", $array_url);
                $array_data[$key]["vod_play_server"] = implode("\$\$\$", $array_server);
                $array_data[$key]["vod_play_note"] = implode("\$\$\$", $array_note);
            }
            $key++;
        }
        $res = ["code" => 1, "msg" => "xml数据获取成功", "data" => $array_data];
        return $res;
    }
    public function vod_xml($param, $html = '')
    {
        $url_param = [];
        $url_param["ac"] = $param["ac"];
        $url_param["t"] = $param["t"];
        $url_param["pg"] = is_numeric($param["page"]) ? $param["page"] : '';
        $url_param["ids"] = $param["ids"];
        $url_param["wd"] = $param["wd"];
        if ($param["ac"] != "list") {
            $url_param["ac"] = "videolist";
        }
        $url = $param["cjurl"];
        if (strpos($url, "?") === false) {
            $url .= "?";
        } else {
            $url .= "&";
        }
        $url .= http_build_query($url_param) . base64_decode($param["param"]);
        $html = mac_curl_get($url);
        if (empty($html)) {
            return ["code" => 1001, "msg" => "连接API资源库失败，通常为服务器网络不稳定或禁用了采集"];
        }
        $xml = @simplexml_load_string($html);
        if (empty($xml)) {
            $labelRule = "<pic>" . "(.*?)" . "</pic>";
            $labelRule = mac_buildregx($labelRule, "is");
            preg_match_all($labelRule, $html, $tmparr);
            $ec = false;
            foreach ($tmparr[1] as $tt) {
                if (strpos($tt, "[CDATA") === false) {
                    $ec = true;
                    $ne = "<pic>" . "<![CDATA[" . $tt . "]]>" . "</pic>";
                    $html = str_replace("<pic>" . $tt . "</pic>", $ne, $html);
                }
            }
            if ($ec) {
                $xml = @simplexml_load_string($html);
            }
            if (empty($xml)) {
                return ["code" => 1002, "msg" => "XML格式不正确，不支持采集"];
            }
        }
        $array_page = [];
        $array_page["page"] = (string) $xml->list->attributes()->page;
        $array_page["pagecount"] = (string) $xml->list->attributes()->pagecount;
        $array_page["pagesize"] = (string) $xml->list->attributes()->pagesize;
        $array_page["recordcount"] = (string) $xml->list->attributes()->recordcount;
        $array_page["url"] = $url;
        $key = 0;
        $array_data = [];
        foreach ($xml->list->video as $video) {
            $array_data[$key]["vod_id"] = (string) $video->id;
            $array_data[$key]["vod_name"] = (string) $video->name;
            $array_data[$key]["vod_remarks"] = (string) $video->note;
            $array_data[$key]["type_name"] = (string) $video->type;
            $array_data[$key]["vod_pic"] = (string) $video->pic;
            $array_data[$key]["vod_lang"] = (string) $video->lang;
            $array_data[$key]["vod_area"] = (string) $video->area;
            $array_data[$key]["vod_year"] = (string) $video->year;
            $array_data[$key]["vod_serial"] = (string) $video->state;
            $array_data[$key]["vod_actor"] = (string) $video->actor;
            $array_data[$key]["vod_director"] = (string) $video->director;
            $array_data[$key]["vod_content"] = (string) $video->des;
            $array_data[$key]["vod_status"] = 1;
            $array_data[$key]["vod_type"] = $array_data[$key]["list_name"];
            $array_data[$key]["vod_time"] = (string) $video->last;
            $array_data[$key]["vod_total"] = 0;
            $array_data[$key]["vod_isend"] = 1;
            if ($array_data[$key]["vod_serial"]) {
                $array_data[$key]["vod_isend"] = 0;
            }
            $array_from = [];
            $array_url = [];
            $array_server = [];
            $array_note = [];
            if ($count = count($video->dl->dd)) {
                $i = 0;
                while ($i < $count) {
                    $array_from[$i] = (string) $video->dl->dd[$i]["flag"];
                    $array_url[$i] = $this->vod_xml_replace((string) $video->dl->dd[$i]);
                    $array_server[$i] = "no";
                    $array_note[$i] = '';
                    $i++;
                }
            } else {
                $array_from[] = (string) $video->dt;
                $array_url[] = '';
                $array_server[] = '';
                $array_note[] = '';
            }
            if (strpos(base64_decode($param["param"]), "ct=1") !== false) {
                $array_data[$key]["vod_down_from"] = implode("\$\$\$", $array_from);
                $array_data[$key]["vod_down_url"] = implode("\$\$\$", $array_url);
                $array_data[$key]["vod_down_server"] = implode("\$\$\$", $array_server);
                $array_data[$key]["vod_down_note"] = implode("\$\$\$", $array_note);
            } else {
                $array_data[$key]["vod_play_from"] = implode("\$\$\$", $array_from);
                $array_data[$key]["vod_play_url"] = implode("\$\$\$", $array_url);
                $array_data[$key]["vod_play_server"] = implode("\$\$\$", $array_server);
                $array_data[$key]["vod_play_note"] = implode("\$\$\$", $array_note);
            }
            $key++;
        }
        $array_type = [];
        $key = 0;
        if ($param["ac"] == "list") {
            foreach ($xml->class->ty as $ty) {
                $array_type[$key]["type_id"] = (string) $ty->attributes()->id;
                $array_type[$key]["type_name"] = (string) $ty;
                $key++;
            }
        }
        $res = ["code" => 1, "msg" => "xml", "page" => $array_page ? $array_page : [], "type" => $array_type, "data" => $array_data];
        return $res;
    }
    public function vod_xml_replace($url)
    {
        $array_url = array();
        $arr_ji = explode("#", str_replace("||", "//", $url));
        foreach ($arr_ji as $key => $value) {
            $urlji = explode("\$", $value);
            if (count($urlji) > 1) {
                $array_url[$key] = $urlji[0] . "\$" . trim($urlji[1]);
            } else {
                $array_url[$key] = trim($urlji[0]);
            }
        }
        return implode("#", $array_url);
    }
    public function getYunDatav10($wd)
    {
        $systemDataUse = $this->systemData;
        $zyUrl = $systemDataUse["vod_yun_macv10_list"];
        $urlData = explode("%%%", $zyUrl);
        foreach ($urlData as $ks => $vs) {
            $u1[] = explode("%", $vs);
        }
        foreach ($u1 as $kq => $vq) {
            $umast[$vq[0]] = $vq[1];
        }
        $mastPlayData = [];
        $mastPlayData["playData"] = [];
        $mastPlayData["dyName"] = $wd;
        foreach ($umast as $kk => $vb) {
            @($zyYun = file_get_contents($vb . "ac/detail/wd/" . $wd));
            $data = json_decode($zyYun, true);
            if (isset($data["list"][0])) {
                $data["list"][0]["list_name"] = $data["list"][0]["type_name"];
                $data["list"][0]["vod_addtime"] = $data["list"][0]["vod_time"];
                $mastPlayData["other"] = $data["list"][0];
                $playData = explode("#", $data["list"][0]["vod_play_url"]);
                foreach ($playData as $k => $v) {
                    if ($v != '') {
                        $uData = explode("\$", $v);
                        $urlDatas[$k]["lianjie"] = $uData[1];
                        $url3[$k]["url"] = $uData[1];
                        $url3[$k]["qishu"] = $uData[0];
                    }
                }
                array_push($mastPlayData["playData"], ["playType" => $kk, "urlData" => $urlDatas, "zongyi" => $url3]);
            }
        }
        return $mastPlayData;
    }
    public function getcxplayData($id, $src)
    {
        $systemDataUse = $this->systemData;
        $zyArr = $systemDataUse["vod_yun_config"];
        $a = explode("%%%", $zyArr);
        foreach ($a as $k => $v) {
            $b[$k] = explode("%", $v);
        }
        $url = $b[$src][1] . "?vodids=" . $id;
        @($data = json_decode(file_get_contents($url), true));
        $dyName = $data["data"][0]["vod_name"];
        if (isset($data["data"][0])) {
            if (strpos($data["data"][0]["vod_url"], ".m3u8")) {
                $playUrlData = explode("\$\$\$", $data["data"][0]["vod_url"]);
                foreach ($playUrlData as $k1 => $v1) {
                    if (strpos($v1, ".m3u8")) {
                        $url1 = str_replace("\r\n", '', $v1);
                    }
                }
                $url2 = explode(".m3u8", $url1);
                foreach ($url2 as $k2 => &$v2) {
                    if ($v2 != '') {
                        $v2 = trim(strrchr($v2, "\$"), "\$") . ".m3u8";
                        $url3[]["lianjie"] = $v2;
                    } else {
                        unset($url2[$k2]);
                    }
                }
            }
        }
        return ["dyName" => $dyName, "other" => $data["data"][0], "playData" => [["playType" => "官方云播", "urlData" => $url3]]];
    }
    public function getcxplayDataone($id)
    {
        $systemDataUse = $this->systemData;
        $url = "http://" . $systemDataUse["vod_zy_list"] . "?vodids=" . $id;
        @($data = json_decode(file_get_contents($url), true));
        $dyName = $data["data"][0]["vod_name"];
        if (isset($data["data"][0])) {
            if (strpos($data["data"][0]["vod_url"], ".m3u8")) {
                $playUrlData = explode("\$\$\$", $data["data"][0]["vod_url"]);
                foreach ($playUrlData as $k1 => $v1) {
                    if (strpos($v1, ".m3u8")) {
                        $url1 = str_replace("\r\n", '', $v1);
                    }
                }
                $url2 = explode(".m3u8", $url1);
                foreach ($url2 as $k2 => &$v2) {
                    if ($v2 != '') {
                        $v2 = trim(strrchr($v2, "\$"), "\$") . ".m3u8";
                        $url3[]["lianjie"] = $v2;
                    } else {
                        unset($url2[$k2]);
                    }
                }
            }
        }
        return ["dyName" => $dyName, "other" => $data["data"][0], "playData" => [["playType" => "官方云播", "urlData" => $url3]]];
    }
    public function getcxplayDatav10($id, $src)
    {
        $systemDataUse = $this->systemData;
        $zyArr = $systemDataUse["vod_yun_macv10_list"];
        $a = explode("%%%", $zyArr);
        foreach ($a as $k => $v) {
            $b[$k] = explode("%", $v);
        }
        $zyurl = $b[$src][1] . "ac/detail/ids/" . $id;
        @($data = json_decode(file_get_contents($zyUrl), true));
        $dyName = $data["list"][0]["vod_name"];
        $playData = explode("#", $data["list"][0]["vod_play_url"]);
        foreach ($playData as $k => $v) {
            if ($v != '') {
                $uData = explode("\$", $v);
                $urlData[]["lianjie"] = $uData[1];
            }
        }
        $data["list"][0]["list_name"] = $data["list"][0]["type_name"];
        $data["list"][0]["vod_addtime"] = $data["list"][0]["vod_time"];
        return ["dyName" => $dyName, "other" => $data["list"][0], "playData" => [["playType" => "官方云播", "urlData" => $urlData]]];
    }
    public function tvOrDongManOrZongYiNew($url)
    {
        error_reporting(0);
        $dyjianjie = file_get_contents($this->nodeHost."/api/api/queryTvs?host=" . $url . "&rehost=" . $this->requestHost);
        $dyjianjie = json_decode($dyjianjie, true);
        $data = array();
        $data["playData"] = $dyjianjie["playData"];
        $data["dyName"] = $dyjianjie["dyName"];
        $data["other"] = ["vod_pic" => $dyjianjie["vod_pic"], "vod_year" => $dyjianjie["vod_year"], "vod_area" => $dyjianjie["vod_area"], "vod_content" => $dyjianjie["vod_content"], "vod_score" => $dyjianjie["vod_score"], "list_name" => $dyjianjie["list_name"], "vod_director" => $dyjianjie["vod_director"], "vod_actor" => $dyjianjie["vod_actor"], "vod_addtime" => date("Y-m-d H:i:s", time())];
        isset($dyjianjie["zongyi"]) ? $data["zongyi"] = $dyjianjie["zongyi"] : [];
        return $data;
    }
    public function tvOrDongManOrZongYi($url)
    {
        error_reporting(0);
        $dyjianjie = file_get_contents($this->nodeHost."/api/api/queryTv?host=" . $url . "&rehost=" . $this->requestHost);
        $dyjianjie = json_decode($dyjianjie, true);
        $yuming = "http://www.360kan.com";
        $player = $yuming . $url;
        $tvinfo = file_get_contents($player);
        $arr = explode("/", $url);
        $mkcmsid = str_replace(".html", '', "{$arr[2]}");
        $mkcmstyle = $arr[1];
        $laiyuan = "#{\"ensite\":\"(.*?)\",\"cnsite\":\"(.*?)\",\"vip\":(.*?)}#";
        preg_match_all($laiyuan, $tvinfo, $laiyuanarr);
        $yuan = $laiyuanarr[1];
        $zyv = "#<em class='top-new'>\\s*(monitor-desc=\"收起往期更多\">)?[\\s\\S]+?<div monitor-desc#";
        $qi = "#<span class='w-newfigure-hint'>(.*?)</span>#";
        $zyimg = "#data-src='(.*?)' alt='(.*?)'#";
        preg_match_all($zyv, $tvinfo, $zyvarr);
        $zylist = implode($glue, $zyvarr[0]);
        $ztlizz = "#<a href='(.*?)' data-daochu=to=(.*?) class='js-link'><div class='w-newfigure-imglink g-playicon js-playicon'>#";
        preg_match_all($ztlizz, $zylist, $zyliarr);
        preg_match_all($qi, $zylist, $qiarr);
        preg_match_all($zyimg, $zylist, $imgarr);
        $zyvi = $zyliarr[1];
        $noqi = $qiarr[1];
        $zypic = $imgarr[1];
        $zyname = $imgarr[2];
        $zcf = implode($tvarr[0]);
        preg_match_all($tvzz1, $zcf, $tvar1);
        $b = $tvar1[3];
        $much = 1;
        $mjk = $mkcms_mjk;
        $videoarray = array();
        $i = 0;
        while ($i < count($yuan)) {
            switch ($yuan[$i]) {
                case "qiyi":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=qiyi&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "爱奇艺", "urlData" => $list));
                    break;
                case "qq":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=qq&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "腾讯", "urlData" => $list));
                    break;
                case "pptv":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=pptv&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "PPTV", "urlData" => $list));
                    break;
                case "imgo":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=imgo&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "芒果TV", "urlData" => $list));
                    break;
                case "sohu":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=sohu&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "搜狐", "urlData" => $list));
                    break;
                case "youku":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=youku&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "优酷", "urlData" => $list));
                    break;
                case "letv":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=letv&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "乐视", "urlData" => $list));
                    break;
                case "cctv":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=cctv&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "CCTV", "urlData" => $list));
                    break;
                case "wasu":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=wasu&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "华数TV", "urlData" => $list));
                    break;
                case "fengxing":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=fengxing&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$x - 1] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "风行", "urlData" => $list));
                    break;
                case "xunlei":
                    if ($mkcmstyle == tv) {
                        $category = "2";
                    } else {
                        $category = "4";
                    }
                    $url = "http://www.360kan.com/cover/switchsite?site=xunlei&id=" . $mkcmsid . "&category=" . $category;
                    $html = file_get_contents($url);
                    $data = json_decode($html, ture);
                    $data = implode('', $data);
                    $tvzzx = "#<div class=\"num-tab-main g-clear\\s*js-tab\"\\s*(style=\"display:block;\")?>[\\s\\S]+?<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">[\\s\\S]+?<\\/div>\r<\\/div>#";
                    $tvzzy = "#<a data-num=\"(.*?)\" data-daochu=\"to=(.*?)\" href=\"(.*?)\">#";
                    preg_match_all($tvzzx, $data, $tvarry);
                    $zcf = implode($glue, $tvarry[0]);
                    preg_match_all($tvzzy, $zcf, $tvarry);
                    $b = $tvarry[3];
                    $playDataarray3 = $b;
                    $playDataarray3 = implode("\$\$", $playDataarray3);
                    $totalLink = count(explode("\$\$", $playDataarray3));
                    $lianjie = $b;
                    $x = 1;
                    while ($x <= $totalLink) {
                        $a = "第 {$x} 集";
                        $vv = $x - 1;
                        $c = $lianjie[$vv];
                        $list[$vv] = $c;
                        $x++;
                    }
                    array_push($videoarray, array("playType" => "迅雷", "urlData" => $list));
                    break;
            }
            $i++;
        }
        $p = 1;
        while ($p <= count($noqi)) {
            $vv = $p - 1;
            $a = $noqi[$vv];
            $c = $zyvi[$vv];
            $b = $zyname[$vv];
            $listz[$vv] = array("qishu" => $a, "lianjie" => $c, "nei" => $b);
            $p++;
        }
        $data = array();
        $data["playData"] = $videoarray;
        $data["dyName"] = $dyjianjie["dyName"];
        $data["other"] = ["vod_pic" => $dyjianjie["vod_pic"], "vod_year" => $dyjianjie["vod_year"], "vod_area" => $dyjianjie["vod_area"], "vod_content" => $dyjianjie["vod_content"], "vod_score" => $dyjianjie["vod_score"], "list_name" => $dyjianjie["list_name"], "vod_director" => $dyjianjie["vod_director"], "vod_actor" => $dyjianjie["vod_actor"], "vod_addtime" => date("Y-m-d H:i:s", time())];
        $data["zongyi"] = $listz;
        return $data;
    }
    public function getDianYing($url)
    {
        $dyjianjie = file_get_contents($this->nodeHost."/api/api/index?host=" . $url . "&rehost=" . $this->requestHost);
        $dyjianjie = json_decode($dyjianjie, true);
        $data["playData"] = $dyjianjie["playData"];
        $data["dyName"] = $dyjianjie["dyName"];
        $data["other"] = ["vod_pic" => $dyjianjie["vod_pic"], "vod_year" => $dyjianjie["vod_year"], "vod_area" => $dyjianjie["vod_area"], "vod_content" => $dyjianjie["vod_content"], "vod_score" => $dyjianjie["vod_score"], "list_name" => $dyjianjie["list_name"], "vod_director" => $dyjianjie["vod_director"], "vod_actor" => $dyjianjie["vod_actor"], "vod_addtime" => date("Y-m-d H:i:s", time())];
        return $data;
    }
    public function xmlToArray($xml)
    {
        if (file_exists($xml)) {
            libxml_disable_entity_loader(false);
            $xml_string = simplexml_load_file($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        } else {
            libxml_disable_entity_loader(true);
            $xml_string = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        }
        $result = json_decode(json_encode($xml_string), true);
        return $result;
    }
    public function getListDataByTypeId($id, $year, $area, $cat, $page)
    {
        if ($id == 1) {
            $url = "https://www.360kan.com/dianying/list?" . "year=" . $year . "&area=" . $area . "&act=all&cat=" . $cat . "&pageno=" . $page;
        } else {
            if ($id == 2) {
                $url = "https://www.360kan.com/dianshi/list?" . "year=" . $year . "&area=" . $area . "&act=all&cat=" . $cat . "&pageno=" . $page;
            } else {
                if ($id == 3) {
                    $url = "https://www.360kan.com/zongyi/list?" . "year=" . $year . "&area=" . $area . "&act=all&cat=" . $cat . "&pageno=" . $page;
                } else {
                    if ($id == 4) {
                        $url = "https://www.360kan.com/dongman/list?" . "year=" . $year . "&area=" . $area . "&act=all&cat=" . $cat . "&pageno=" . $page;
                    } else {
                        $url = "https://www.360kan.com/dianying/list?" . "year=" . $year . "&area=" . $area . "&act=all&cat=" . $cat . "&pageno=" . $page;
                    }
                }
            }
        }
        $info = $this->curlData($this->nodeHost."/api/index/get360dyauth", array("host" => $url));
        $data = json_decode($info, true);
        return $data;
    }
    public function replaceTitle($wd)
    {
        $match = config("title_replace");
        $name = trim(str_replace($match, '', $wd));
        return $name;
    }
    public function getBookHot()
    {
        $ver = file_get_contents($this->nodeHost."/api/book/type" . "&rehost=" . $this->requestHost);
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function getBookList($type, $page)
    {
        $ver = file_get_contents($this->nodeHost."/api/book/bookList" . "?type=" . $type . "&page=" . $page . "&rehost=" . $this->requestHost);
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function getBookDetail($bookId)
    {
        $ver = file_get_contents($this->nodeHost."/api/book/bookDetail" . "?bookId=" . $bookId . "&rehost=" . $this->requestHost);
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function getBookZhang($zId)
    {
        $ver = file_get_contents($this->nodeHost."/api/book/bookPlay" . "?zId=" . $zId . "&rehost=" . $this->requestHost);
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function getHuyaTypeData()
    {
        $a = base64_encode($_SERVER["HTTP_HOST"] . "|" . $_SERVER["SERVER_NAME"]);
        @($ver = file_get_contents($this->nodeHost."/api/huya/getType" . "?rehost=" . $a));
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function getHuyaListData($cid, $page)
    {
        $a = base64_encode($_SERVER["HTTP_HOST"] . "|" . $_SERVER["SERVER_NAME"]);
        @($ver = file_get_contents($this->nodeHost."/api/huya/index" . "?cid=" . $cid . "&page=" . $page . "&rehost=" . $a));
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function getBooksearData()
    {
        $a = base64_encode($_SERVER["HTTP_HOST"] . "|" . $_SERVER["SERVER_NAME"]);
        @($ver = file_get_contents($this->nodeHost."/api/book/searchBook" . "&rehost=" . $a));
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
    public function getActorListData($actor)
    {
        $a = base64_encode($_SERVER["HTTP_HOST"] . "|" . $_SERVER["SERVER_NAME"]);
        @($ver = file_get_contents($this->nodeHost."/api/api/getActor?actor=" . $actor . "&rehost=" . $a));
        if ($ver) {
            return json_decode($ver, true);
        } else {
            return [];
        }
    }
}
?>