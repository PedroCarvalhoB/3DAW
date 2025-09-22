<!DOCTYPE html>
<html>
<head>
</head>
<body>

<a href='crud_pergunta.php'>Criar nova pergunta</a>

<h1>Listar Perguntas</h1>

<table>
    <!-- <tr><th>Pergunta</th><th>TipoResposta</th><th>Certa</th></tr> -->
<?php
    $arqDisc = fopen("../perguntas.txt","r") or die("erro ao abrir arquivo");

    $linha = fgets($arqDisc);
    $colunaDados = explode(";", $linha);
    echo '<tr><th>'.$colunaDados[0].'</th><th>'.$colunaDados[1].'</th><th>'.$colunaDados[2].'</th><th>'.$colunaDados[3].'</th><th>alibaba</th></tr>';
 
    while(!feof($arqDisc)) {
        $linha = fgets($arqDisc);
        $colunaDados = explode(";", $linha);
 
        echo "<tr><td>" . $colunaDados[0] . "</td>" .
            "<td>" . $colunaDados[1] . "</td>" .
            "<td>" . $colunaDados[2] . "</td>".
            "<td>" . $colunaDados[3] . "</td>".
            "<td> <a href='editar_pergunta.php&id='".$colunaDados[0]."'>Editar</td></tr>";
    }
 
   fclose($arqDisc);
?>
</table>
<p><?php echo $msg ?></p>
<br>
</body>
</html>

