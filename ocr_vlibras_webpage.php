<?php

use thiagoalessio\TesseractOCR\TesseractOCR;

require_once "vendor/autoload.php";
require 'vendor/autoload.php';




class LibrasTranslate
{
  function main($url_file)
  {

    $isImageFile = $this->isImageFile($url_file);
    // empty = is video
    if (empty($isImageFile)) {
      return $this->download_and_get_audio_file($url_file);
    } else {
      return $this->get_text_image($url_file);
    }
  }

  function isImageFile($url_file)
  {
    $info = pathinfo($url_file);
    return in_array(
      strtolower($info['extension']),
      array("jpg", "jpeg", "gif", "png", "bmp")
    );
  }

  function download_and_get_audio_file($url_file)
  {

    $file_name = 'video_' . time();
    $file_video_name = "video_folder/{$file_name}" . '.mp4';
    $file_audio_name = "audios_folder/{$file_name}" . '.mp3';

    //Donwload video and save in /videos_folder
    try {
      file_put_contents($file_video_name, file_get_contents($url_file));
    } catch (Exception $e) {
      echo 'Erro download file: ',  $e->getMessage(), "\n";
    }

    // extract audio and save in /audios_folder
    $this->extract_audio($file_video_name, $file_audio_name);

    // exec python script to get text from audio
    $text_from_audio = $this->exec_python_speech_to_text($file_audio_name);

    // delet files video and audio
    unlink($file_video_name);
    unlink($file_audio_name);

    return $text_from_audio;
  }

  function extract_audio($file_video_name,   $file_audio_name)
  {
    try {
      $ffmpeg = FFMpeg\FFMpeg::create();
      $video = $ffmpeg->open($file_video_name);
      // Set an audio format
      $audio_format = new FFMpeg\Format\Audio\Mp3();

      // Extract the audio into a new file as mp3
      $video->save($audio_format,  $file_audio_name);
    } catch (Exception $e) {
      echo 'Erro extract file audio: ',  $e->getMessage(), "\n";
    }
  }


  function exec_python_speech_to_text($file_audio_name)
  {
    // exec python script. Need to parse -i argument to script find the file
    exec("python3 speech-to-text.py -i {$file_audio_name}", $result);
    $result =  implode("\n", $result);

    return $result;
  }

  function get_text_image($url_file)
  {

    $file_name = 'image_' . time();
    $file_image_name = "images_folder/{$file_name}" . '.jpg';

    //Donwload image and save in /image_folder
    try {
      file_put_contents($file_image_name, file_get_contents($url_file));
    } catch (Exception $e) {
      echo 'Erro download image file: ',  $e->getMessage(), "\n";
    }

    $text = '';

    $text = (new TesseractOCR($file_image_name))
      ->lang('por')
      ->run();
    unlink($file_image_name);
    return $text;
  }
}

// use to test
// image_url: https://portal.fiocruz.br/sites/portal.fiocruz.br/files/imagensPortal/uso_de_mascaras_para_protecao_coletiva.jpg
// video_url: https://joaovideolibras.web.app/video_test_libras.mp4


// Start
/*
$libras_translate = new LibrasTranslate();
$result_libras_tanslate = $libras_translate->main('https://joaovideolibras.web.app/video_test_libras.mp4');
echo $result_libras_tanslate;
*/



$url = $_GET["url"];

$libras_translate = new LibrasTranslate();

?>

<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous" />

  <title>Hello, world!</title>
</head>

<body>

  <div class="card" style="width: 50%;">


    <?php
    $isImageFile = $libras_translate->isImageFile($url);
    // empty = is video
    if (empty($isImageFile)) {
      echo "<video controls autoplay style='height: 500px;'>
     <source src='{$url} ' type='video/mp4'>  
 
   </video>";
    } else {
      echo  "<img class='card-img-top'src='{$url}' alt='Card image cap'>";
    }

    ?>


    <div class="card-body">
      <p class="card-text" style="font-size: 30px;">
        <?php
        $result_libras_tanslate = $libras_translate->main($url);
        echo htmlspecialchars($result_libras_tanslate);

        ?>
      </p>
    </div>
  </div>




  <script>
    document.addEventListener("DOMContentLoaded", function(event) {
      const wait_time = 1000;

      setTimeout(() => {
        document.getElementsByClassName("active")[0].click();


      }, wait_time);
    });
  </script>

  <div vw class="enabled" style='height: 100%; top:35% '>
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper style="width: 500px;">
      <div class="vw-plugin-top-wrapper"></div>
    </div>
  </div>
  <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
  <script>
    new window.VLibras.Widget("https://vlibras.gov.br/app");
  </script>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>

</html>