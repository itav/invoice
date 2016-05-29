<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title> <?php $view['slots']->output('title', 'Invoice') ?></title>
        <script src="/js/jquery-1.12.4.min.js" type="text/javascript"></script>
        <script src="/js/jquery-ui.min.js" type="text/javascript" ></script>

        <link rel="stylesheet" href="/js/jquery-ui.min.css">
        <link rel="stylesheet" href="/js/jquery-ui.theme.min.css">
        <link rel="stylesheet" href="/js/jquery-ui.structure.min.css">

    </head>
    <body>
        <?php
        $view['slots']->output('_content')
        ?>
    </body>

</html>
