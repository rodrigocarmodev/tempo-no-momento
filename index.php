<?php

require('./config/token.php');

$cidade = $_GET['cidade'] && !empty($_GET['cidade']) ? $_GET['cidade'] : null;
$estado = $_GET['estado'] && !empty($_GET['estado']) ? $_GET['estado'] : null;

/* 
To add a new state/city is necessary do a put request: 

curl -X PUT \                                                                                                          
     'http://apiadvisor.climatempo.com.br/api-manager/user-token/d5f8e8f812af06360ef4ea131c7f7102/locales' \
         -H 'Content-Type: application/x-www-form-urlencoded' \
         -d 'localeId[]=:id'
*/

if ($cidade !== null && $estado !== null) {
  $curl = curl_init();
  $url = 'http://apiadvisor.climatempo.com.br/api/v1/locale/city?name=' . urlencode($cidade) . '&state=' . urlencode($estado) . '&token=' . urlencode($token);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //set to return string
  $dados_cidade = json_decode(curl_exec($curl));
  curl_close($curl);

  $id_cidade = $dados_cidade[0] ? (int)$dados_cidade[0]->id : null;

  if ($id_cidade !== null) {
    $curl = curl_init();
    $url2 = "http://apiadvisor.climatempo.com.br/api/v1/weather/locale/$id_cidade/current?token=$token";
    curl_setopt($curl, CURLOPT_URL, $url2);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //set to return string
    $dados_tempo = json_decode(curl_exec($curl));
    curl_close($curl);

    // Verifica a previsao
    $curl3 = curl_init();
    $url3 = "http://apiadvisor.climatempo.com.br/api/v1/forecast/locale/$id_cidade/days/15?token=$token";
    curl_setopt($curl3, CURLOPT_URL, $url3);
    curl_setopt($curl3, CURLOPT_RETURNTRANSFER, 1); //set to return string
    $dados_previsao = json_decode(curl_exec($curl3));
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tempo by Rodrigo Carmo :]</title>
  <link rel="stylesheet" href="src/css/main.css" />
</head>

<body>
  <div class="main">
    <div class="card">
      <h2 style="text-align: center;"><a href="http://localhost:8888/tempo-no-momento/">Tempo</a></h2>
      <div>
        <form action="" style="text-align: center; line-height: 30px;">
          <label for="estado">Digite o Estado:</label>
          <input type="text" name="estado" />
          <label for="cidade">Cidade:</label>
          <input type="text" name="cidade" />
          <input type="submit" class="btn-verify" style="margin-left: 5px;" value="Verificar" />
        </form>
      </div>
      <?php if ($dados_tempo && !$dados_tempo->error) { ?>
        <div class="card-info">
          <img src="src/img/weather/<?= $dados_tempo->data->icon ?>.png" alt="img" width="100">
          <p>O tempo agora em <?= $dados_tempo->name ?> é de <?= $dados_tempo->data->temperature ?>º. <?= $dados_tempo->data->condition ?>. <br> A previsão de temperatura mínima é de <?= $dados_previsao->data[0]->temperature->min ?>º e máxima de <?= $dados_previsao->data[0]->temperature->max ?>º.</p>
        </div>
      <?php } else { ?>
        <div style="text-align: center; <?php if (!$cidade && !$estado) { echo "display: none"; } ?>">
          <p>Sem informações para exibir.</p>
        </div>
      <?php } ?>
      <div style="<?php if (!$cidade && !$estado) { echo "display: none"; } ?>">
        <h2 style="text-align: center;">Próximos dias</h2>
        <div style="display: flex; flex-direction: row; justify-content: center; align-items: center;">
          <?php
          if ($dados_previsao && !$dados_previsao->error) {
            for ($i = 1; $i < 4; $i++) { ?>
              <div class="card-small">
                <strong><?= $dados_previsao->data[$i]->date_br ?></strong><br>
                <div class="card-img"><img src="src/img/weather/<?= $dados_previsao->data[$i]->text_icon->icon->day ?>.png" alt="img" width="100">
                </div>
                <strong>Mín:</strong> <?= $dados_previsao->data[$i]->temperature->min ?>º - <strong>Máx:</strong> <?= $dados_previsao->data[$i]->temperature->max ?>º <br><br>
                <?= $dados_previsao->data[$i]->text_icon->text->phrase->reduced ?>
              </div>
            <?php }
          } else { ?>
            <div style="text-align: center;">
              <p>Sem informações para exibir.</p>
            </div>
          <?php } ?>
        </div>
      </div>

    </div>
  </div>
</body>

</html>