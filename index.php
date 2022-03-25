<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Page Title</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <style>
        *{
            box-sizing: border-box;
        }
        html, body{
            height: 100%;
            padding: 0;
            margin: 0;
        }
        main{
            position: relative;
            height: 100%;
            background: rgb(73, 167, 255);
        }
        form {
            position: absolute;
            top: calc(50% - 120px);
            left: calc(50% - 150px);
            width: 300px;
            height: 240px;
            padding: 5px 20px;
            background: lightgray;
            box-shadow: 10px 10px 10px #666;
        }
        form div.input-group{
            margin-bottom: 20px;
        }
        form div.input-group h2{
            color: rgb(50, 50, 255);
        }
        form div.input-group label{
            font-size: 20px;
        }
        form div.input-group input{
            width: 100%;
            padding: 5px;
            font-size: 20px;
            border-radius: 20px;
        }
        form div.input-group button{
            width: 100%;
            background: lightgreen;
            /* border: 1px solid darkgreen; */
            padding: 10px;
            border-radius: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <main>
        <form method="POST" action="gameroom.php" id="form-login">
            <div class="input-group">
                <h2>Gartic PHP</h2>
            </div>
            <div class="input-group">
                <label>Nickname</label>
                <input required name="username">
            </div>
            <div class="input-group">
                <button type="submit">Entrar</button>
            </div>
        </form>
    </main>
</body>
</html>