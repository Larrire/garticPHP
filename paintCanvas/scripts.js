
document.addEventListener('DOMContentLoaded', ()=>{

    var conn = new WebSocket('ws://localhost:8081');

    let myId;

    let painting = false;

    let timer = null;
        
    conn.onopen = function(e) {
        console.log("Connection established!");
        const msg = JSON.stringify( {type:'setName', name: username} );
        conn.send(msg);
    };
    
    conn.onmessage = function(e) {
        data = JSON.parse(e.data);
        switch( data.type ){
            case 'draw':
                context.strokeStyle = data.color;
                drawLine({
                    pos: {x:data.pos.x, y: data.pos.y}, 
                    prevPos: {x: data.prevPos.x, y:data.prevPos.y},
                });
            break;
            case 'clear':
                clearScreen();
            break;
            case 'updatePlacar':
                updatePlacar(data.players);
            break;
            case 'setId':
                myId = data.id;
            break;
            case 'chat':
                chat(data);
            break;
            case 'setClientTimer':
                startTimer( data );
            break;
            case 'changeScreenPermition':
                painting = data.status;
            break;
            case 'InvalidName':
                alert( data.msg )
                window.location = 'index.php';
            break;
            case 'newTurn':
                setTimeout(()=>{
                    // const msg = JSON.stringify( {type:'setName', name: username} );
                    // conn.send(msg);
                }, 60000)
            break;
            case 'endTurn':
                setTimeout(()=>{
                    const msg = JSON.stringify( {type:'setName', name: username} );
                    conn.send(msg);
                }, 60000)
            break;
        }
    };

    const updatePlacar = (players) => {
        let divPlacar = '';

        players.map( (player, index) => {
            // let rank = '';
            // if( index === 0 ){
            //     rank = 'golden';
            // } else if ( index === 1 ){
            //     rank = 'silver';
            // } else if ( index === 2 ) {
            //     rank = 'bronze';
            // }
            divPlacar += `
                <!-- Jogador -->
                <div id=${player.id} class="jogador-placar ${(player.id === myId) && 'my-player-card'}">
                    <div class="jogador-img"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/7c/User_font_awesome.svg/1200px-User_font_awesome.svg.png"></div>
                    <div class="jogados-dados">
                        <div><b>${player.name}</b></div>
                        <div>${player.pts}pts</div>
                    </div>
                </div>
            `;
        } );

        const div = document.querySelector('.div-placar');
        div.innerHTML = divPlacar;  
    }

    const chat = (msg) => {
        const divChatMsgs = document.querySelector('#div-chat');
        divChatMsgs.innerHTML += `
            <div>
                <p>
                <b>${msg.name} ${ (msg.name !== '') ? ':' : '' }</b>
                ${msg.msg}
                </p>
            </div>
        `
    };

    const clearScreen = () => {
        context.clearRect(0, 0, screen.width, screen.height);
    };

    const startTimer = (data) => {
        const timer = document.querySelector('#timer');
        timer = setInterval(()=>{
            if( data.time === 0 ){
                clearInterval(timer);
            } else {
                data.time--;
                timer.value = `${((data.time/60)^0).toString().padStart(2, '0')}:${(data.time%60).toString().padStart(2, '0')}`;
            }
        }, 1000)
    }

   setTimeout(()=>{
    const teste = {
        type: 'setTimerClient',
    };
    
    const jsonteste = JSON.stringify(teste);
    conn.send(jsonteste);
   }, 0)

    // menu
    document.querySelector('#color').addEventListener('change', ()=>{
        const theInput = document.getElementById("color");
        context.strokeStyle = theInput.value;
        // pincel.pos = {x:0, y:0};
    });

    document.querySelector('#form-chat').addEventListener('submit', (e)=>{
        e.preventDefault();
        const msg = {
            'type': 'chat',
            'name': username,
            'msg': document.querySelector('#input-form-chat').value
        }
        if( document.querySelector('#input-form-chat').value.trim() !== '' )
        {
            conn.send( JSON.stringify(msg) );
            document.querySelector('#input-form-chat').value = '';
        }
    });

    document.querySelector('#botao-limpar').addEventListener('click', ()=>{
        const row = {
            type: 'clear',
        };
        const jsonRow = JSON.stringify(row);
        conn.send(jsonRow);
    });

    const pincel = {
        ativo: false,
        movendo: false,
        pos: {x:0, y:0},
        prevPos: null,
    }

    const screen = document.getElementById("screen");
    const context = screen.getContext("2d");
    screen.width = 800
    screen.height = 400
    context.lineWidth = 4;
    // context.strokeStyle = 'red';

    const drawLine = ( line ) => {
        context.beginPath();
        context.moveTo( line.prevPos.x, line.prevPos.y );
        context.lineTo( line.pos.x, line.pos.y );
        context.stroke();
    }

    screen.onmousedown = (event) => { pincel.ativo = true };
    screen.onmouseup = (event) => { pincel.ativo = false };

    screen.onmousemove = (event) => {
        pincel.pos.x = event.offsetX;
        pincel.pos.y = event.offsetY;
        pincel.movendo = true;
    };

    const loop = () => {
        if( pincel.ativo && pincel.movendo && pincel.prevPos && painting ){
            const row = {
                type: 'draw',
                color: context.strokeStyle,
                pos: {x:pincel.pos.x, y: pincel.pos.y}, 
                prevPos: {x: pincel.prevPos.x, y:pincel.prevPos.y}
            };
            
            const jsonRow = JSON.stringify(row);
            conn.send(jsonRow);
            pincel.movendo = false;
        }
        pincel.prevPos = { ...pincel.pos };

        setTimeout(loop, 10);
    }

    loop();
});
