<?php
    if( isset($_POST['username']) ){
?>
    <script>
        const username = "<?php echo $_POST['username'] ?>"
    </script>
<?php 
} else { 
    header('Location: index.php');
}
?>

<html>
<head>
    <title>Paint Canvas</title>
    <script src="https://use.fontawesome.com/2c04f3128b.js"></script>
    <meta charset="utf-8">
    <link rel="stylesheet" href="paintCanvas/styles.css">
    <script src="paintCanvas/scripts.js"></script>
    
</head>
<body>
    <main>
    <aside class="menu">
        <h1>Menu</h1>
        <hr>
        <div class="div-opcoes">
            <label class="botao-menu" for="color">Cores <input class="d-none" type="color" id="color" name="color"></label>
            
            <button id="botao-limpar" class="botao-menu">Limpar</button>
            <button id="botao-limpar" class="botao-menu">Preencher</button>
            <div id="botoes-controle-versao">
                <button class="botao-menu"><</button>
                <button class="botao-menu"><i class="fas fa-redo"></i></button>
            </div>
        </div>
    </aside>

    <div>
        <div id="div-screen">
            <canvas id="screen"></canvas>
            <div>
                <input disabled id="timer" type="text" value="00:00">
            </div>        
        </div>
        <div class="div-chats">
            <div class="div-chat">
                <h4>Respostas</h4>
                <div class="div-chat-msgs">
                    <div>
                        <b>Whindersson: </b><span>Alguma coisa</span>
                    </div>
                </div>
                <input>
            </div>
            <div class="div-chat">
                <h4>Chat</h4>
                <div id="div-chat" class="div-chat-msgs">
                </div>
                <form id="form-chat">
                    <input autocomplete="off" id="input-form-chat"><button>></button>
                </form>
            </div>
        </div>
    </div>

    <aside class="menu">
        <h1>Placar</h1>
        <hr>
        <div class="div-placar">
        </div>
    </aside>
    </main>

</body>
</html>