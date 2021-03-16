
<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);

$counter = 0;
$arr_data = array();

$pid = $_POST['playlist'];
$yt_api = 'AIzaSyDUF3v8nCibiEEEL3677lSfjPMKWMNdPuQ';
print $pid;

function fetch_list1($np, $arr_data)
{
    $playlist_id = $GLOBALS['pid'];

    $yt_api_key = $GLOBALS['yt_api'];
    // $yt_api_key = 'AIzaSyAFjI6016obD-uIlCdR-GvhmhvDwRTnBJc';
    if ($np != '')
        $url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet%2CcontentDetails&maxResults=50&playlistId=" . $playlist_id . "&key=" . $yt_api_key . "&pageToken=" . $np;
    else
        $url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet%2CcontentDetails&maxResults=50&playlistId=" . $playlist_id . "&key=" . $yt_api_key;

    $data1 = file_get_contents($url);
    $decoded_data = json_decode($data1, true);

    if ($decoded_data['nextPageToken'])
        $npt = $decoded_data['nextPageToken'];

    $newArray = $decoded_data['items'];

    foreach ($newArray as $key => $value) {
        if ($value['snippet']['title'] != 'Private video') {

            $vid_id = $value['contentDetails']['videoId'];
            $video_data_url = "https://www.googleapis.com/youtube/v3/videos?part=statistics&part=status&part=contentDetails&id=" . $vid_id . "&key=" . $yt_api_key;
            $video_raw_data = file_get_contents($video_data_url);
            $video_data = json_decode($video_raw_data, true);
            // print_r($video_data);
            // print '<br><br>'.$video_data['items'][0]['status']['embeddable'];

            if ($video_data['items'][0]['status']['embeddable'] && $video_data['items'][0]['status']['privacyStatus'] == "public") {
                $nAkey = $GLOBALS['counter']++;

                $vl = $video_data['items'][0]['contentDetails']['duration'];
                // $vl1 = substr($vl, 2, 1).":".substr($vl, 4, 2);

                // preg_match_all('!\d+!', $vl, $matches);
                // print_r($matches);
                // $vl1 = $matches[0][0].":".$matches[0][1];

                $arr_data[$nAkey]['title'] = $value['snippet']['title'];
                $arr_data[$nAkey]['imgurl'] = $value['snippet']['thumbnails']['high']['url'];
                $arr_data[$nAkey]['publishedAt'] = $value['contentDetails']['videoPublishedAt'];
                $arr_data[$nAkey]['position'] = $value['snippet']['position'];
                $arr_data[$nAkey]['videoId'] = $value['contentDetails']['videoId'];
                $arr_data[$nAkey]['videoLength'] = $vl;
                $arr_data[$nAkey]['viewCount'] = intval($video_data['items'][0]['statistics']['viewCount']);
                $arr_data[$nAkey]['likeCount'] = intval($video_data['items'][0]['statistics']['likeCount']);
                $arr_data[$nAkey]['dislikeCount'] = intval($video_data['items'][0]['statistics']['dislikeCount']);
                $arr_data[$nAkey]['commentCount'] = intval($video_data['items'][0]['statistics']['commentCount']);
                print '<b> Video # ' . $nAkey . ' </b>added to db -> ' . $value['snippet']['title'] . '<br>';
            }
        }
    }

    if ($npt) {
        $arr_data = fetch_list1($npt, $arr_data);
    }

    return $arr_data;
}


$nextpage = '';
$d1 = fetch_list1($nextpage, $arr_data);
//   header('Content-Type: application/json');
$someJSON = json_encode($d1, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
// file_put_contents('jsonFile1.json', $someJSON);
file_put_contents($pid . '.json', $someJSON);
// echo '<br><br>'.$someJSON;

if ($d1) {
    $ifPid = null;
    if (file_exists('playlists.json')) {
        $current_data = file_get_contents('playlists.json');
        $array_data = json_decode($current_data, true);
        //  print_r ($array_data);
        $ifPid = strval(array_search($pid, array_column($array_data, 'playlistId')));
        print "<br>" . $ifPid . " :::::::::::<br>";
        if ($ifPid != null) {
            // if(in_array($pid, $array_data, true)){
            print '<br><br><b>Play List already exists</b>';
            exit;
        }
    }
    $pListUrl = "https://www.googleapis.com/youtube/v3/playlists?part=snippet&id=" . $pid . "&key=" . $GLOBALS['yt_api'];
    $pList = json_decode(file_get_contents($pListUrl), true);
    // $decoded_data = json_decode($data1, true);

    $extra = array(
        'playlistId' => $pid,
        'title' => $pList['items'][0]['snippet']['title'],
        'imgurl' => $pList['items'][0]['snippet']['thumbnails']['high']['url'],
        'videoCount' => sizeof($d1)
    );
    print_r($array_data);   
    $array_data[] = $extra;
    $final_data = json_encode($array_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    file_put_contents('playlists.json', $final_data);
}

?>
