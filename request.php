<?php

error_reporting(E_ALL & E_NOTICE);

$users = ['apple', 'ball', 'cat', 'dog'];

function response($data, $status, $message)
{
    $array_data = ['data' => $data, 'status' => $status, 'message' => $message];
    return json_encode($array_data);
}

/**
 * function to get playlist.
 */
if ($_POST['request_type'] == 'get_playlists') {
    $yt_id = $_POST['yt_id'];

    $array_data = [];
    $authenticated = false;

    if (in_array($yt_id, $users)) {
        $authenticated = true;
    }

    if (!file_exists('playlists.json')) echo response([], 404, 'playlist empty');

    if ($authenticated) {
        $playlists = file_get_contents('playlists.json');
        $array_data = json_decode($playlists);

        echo response($array_data, 200, 'success');
    } else {
        echo response([], 403, 'user not found');
    }

    return;
}
/**
 *  function to delete a playlist from database. 
 */
if ($_POST['request_type'] == 'delete_playlist') {
    $playlistId = $_POST['playlist_id'];

    // echo 'hello delete 
    if (!file_exists('playlists.json')) {
        echo response([], 404, 'playlist empty');
        return;
    }

    // print_r($playlistId);
    // echo "\n";
    $array_data = json_decode(file_get_contents('playlists.json'));
    // print_r($array_data[0]->playlistId);

    $new_data = [];

    foreach ($array_data as $key => $value) {
        if ($value->playlistId != $playlistId) {
            $new_data[] = $value;
        }
    }

    $d = json_encode(array_values($new_data),  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    file_put_contents('playlists.json', $d);

    echo response($new_data, 200, 'list deleted');

    return;
}

/**
 * function to refresh playlist. 
 */

$counter = 0;
$yt_api = 'AIzaSyDUF3v8nCibiEEEL3677lSfjPMKWMNdPuQ';
$pid = '';

if ($_POST['request_type'] == 'refresh_playlist') {
    $arr_data = array();

    $GLOBALS['pid'] = $_POST['playlist_id'];
    // print $pid;
    include('./functions.php');


    $nextpage = '';

    try {

        $d1 = fetch_list1($nextpage, $arr_data);
        //   header('Content-Type: application/json');
        $someJSON = json_encode($d1, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        // file_put_contents('jsonFile1.json', $someJSON);
        file_put_contents($pid . '.json', $someJSON);
        // echo '<br><br>'.$someJSON;

        if ($d1) {
            $ifPid = null;
            $pListUrl = "https://www.googleapis.com/youtube/v3/playlists?part=snippet&id=" . $pid . "&key=" . $GLOBALS['yt_api'];
            $pList = json_decode(file_get_contents($pListUrl), true);
            // $decoded_data = json_decode($data1, true);
            if (file_exists('playlists.json')) {
                $current_data = file_get_contents('playlists.json');
                $array_data = json_decode($current_data, true);
                //  print_r ($array_data);
                $ifPid = strval(array_search($pid, array_column($array_data, 'playlistId')));
                // print "<br>" . $ifPid . " :::::::::::<br>";
                if ($ifPid != null) {
                    $i = array_search($pid, array_column($array_data, 'playlistId'));

                    $array_data[$i]['playlistId'] = $pid;
                    $array_data[$i]['title'] = $pList['items'][0]['snippet']['title'];
                    $array_data[$i]['imgurl'] = $pList['items'][0]['snippet']['thumbnails']['high']['url'];
                    $array_data[$i]['videoCount'] = sizeof($d1);

                    $final_data = json_encode(array_values($array_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                    file_put_contents('playlists.json', $final_data);
                    echo response($array_data[$i], 200, 'database updated2');
                    exit;
                    // print($pid);
                }
            }

            echo response([], 404, 'playlist not found');


            // $extra = array(
            //     'playlistId' => $pid,
            //     'title' => $pList['items'][0]['snippet']['title'],
            //     'imgurl' => $pList['items'][0]['snippet']['thumbnails']['high']['url'],
            //     'videoCount' => sizeof($d1)
            // );
            // $array_data[] = $extra;

            // $final_data = json_encode(array_values($array_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            // file_put_contents('playlists.json', $final_data);


            // echo response($array_data, 200, 'database updated1');
            // exit;
            // }

            exit;
        }
    } catch (Throwable $th) {
        echo response($th->getTrace(), $th->getCode(), $th->getMessage());
    }
}
