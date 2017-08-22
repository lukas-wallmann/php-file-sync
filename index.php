<?php
  $modus=rq("m","read");

  if($modus=="read"){
    $d=rq("d",".");
    $dirs=scandir($d);
    $files=array();
    foreach ($dirs as &$dir) {
      if($dir!="." && $dir!=".."){
        $rp=$d."/".$dir;
        array_push($files,array($dir,is_dir($rp),filemtime($rp)));
      }
    }
    echo json_encode($files);
  }

  if($modus=="get"){
    $filename = rq("w");
    $handle = fopen($filename, "r") or die("someshit");
    $contents = fread($handle, filesize($filename));
    fclose($handle);
    echo $contents;
  }

  if($modus=="syncdown"){
    $from=rq("f");
    $path=".";
    handlesync($from,$path);
  }

  function handlesync($from,$path){
    $log=array();
    array_push($log,"<br><b>connect server:</b>".$from."?d=".$path."<br>");
    $list=json_decode(file_get_contents($from."?d=".$path));
    foreach($list as &$entry){
      $file=$path."/".$entry[0];
      if(file_exists($file)){
        array_push($log,"file exists:".$file);
        if(filemtime($file)<$entry[2]){
          array_push($log,"file on server is newer:".$file);
          if($entry[1]==true){
            handlesync($from,$file);
          }else{
            download($from,$file);
          }
        }else{
          array_push($log,"file on server is older:".$file);
          if($entry[1]==true){
            handlesync($from,$file);
          }
        }
      }else{

        if($entry[1]){
          mkdir($file);
          array_push($log,"dir was created:".$file);
          handlesync($from,$file);
        }else{
          array_push($log,"file dont exists:".$file);
          download($from,$file);
        }
      }
    }
    echo join("<br>",$log);
  }


  function download($from,$file){
    echo "download file:".$file." : ".$from."?m=get&w=".$file."<br>";
    file_put_contents($file,file_get_contents($from."?m=get&w=".$file));
  }

  function rq($name,$val=""){
    return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $val;
  }
?>
