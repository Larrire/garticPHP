<?php
    namespace Rafa\Socket;

    use Ratchet\MessageComponentInterface;
    use Ratchet\ConnectionInterface;
    
    class Game implements MessageComponentInterface {
        protected $clients;
        protected $lines;
        protected $gameStarted;
        protected $turnActive;
        protected $lastTimerSet;
    
        public function __construct() {
            $this->clients = new \SplObjectStorage;
            $this->lines = [];
            $this->gameStarted = false;
            $this->turnActive = false;
            $this->lastTimerSet = 0;
        }

        public function onOpen(ConnectionInterface $conn) {
            // Store the new connection to send messages to later
            $conn->player = (Object)[
                'name' => 'New Player',
                'pts' => 0,
                'id' => $conn->resourceId,
            ];

            $this->clients->attach($conn);

            echo $this->gameStarted;
            
            $this->startNewGame();

            $conn->send( json_encode( (Object)['type'=> 'setId', 'id'=> $conn->resourceId]) );

            foreach ($this->lines as $line) {
                $conn->send( json_encode($line) );
            }
            
            $this->setClientTimer($conn);
        }
    
        public function onMessage(ConnectionInterface $from, $msg) {
            $msg = json_decode($msg);
            switch( $msg->type ){
                case 'draw':
                    $this->draw($msg);
                break;
                case 'clear':
                    $this->clearScreen($msg);
                break;
                case 'setName':
                    $this->setName($from, $msg);
                break;
                case 'changeScreenPermition':
                    $this->changeScreenPermition($msg);
                break;
                case 'chat':
                    $this->chat($msg);
                break;
                case 'setTimerClient':
                    $msg = (Object)[
                        'type' => 'setTimerClient',
                        'time' => 60,
                    ];
                    foreach ($this->clients as $client) {
                        // The sender is not the receiver, send to each client connected
                        $client->send( json_encode( $msg) );
                    }
                break;
            }
        }
    
        public function onClose(ConnectionInterface $conn) {
            // The connection is closed, remove it, as we can no longer send it messages
            $name = ($this->clients->offsetGet($conn)->player->name) ? $this->clients->offsetGet($conn)->player->name : false;

            $this->clients->detach($conn);

            if( $name ){     
                $msg = (Object)[
                    'name' => '',
                    'msg' => "$name Saiu da sala"
                ];
                $this->chat($msg);
            }

            $this->updatePlacar();
    
            echo "Connection {$conn->resourceId} has disconnected\n";
        }
    
        public function onError(ConnectionInterface $conn, \Exception $e) {
            echo "An error has occurred: {$e->getMessage()}\n";
    
            $conn->close();
        }

        // Message functions
        private function draw($msg){
            array_push($this->lines, $msg);
            foreach ($this->clients as $client) {
                // The sender is not the receiver, send to each client connected
                $client->send( json_encode( $msg) );
            }
        }

        private function setTimer($time){
            $this->lastTimerSet = time() + $time;
        }

        private function setClientTimer($client){
            if( $this->lastTimerSet !== 0 ){
                $time = $this->lastTimerSet - time();
                $msg = (Object)[
                    'type' => 'setClientTimer',
                    'time' => $time
                ];
                $client->send( json_encode( $msg) );
            }
        }

        private function chat($data){
            $msg = (Object)[
                'type' => 'chat',
                'msg' => $data->msg,
                'name' => $data->name
            ];
            foreach ($this->clients as $client) {
                // The sender is not the receiver, send to each client connected
                $client->send( json_encode( $msg) );
            }
        }

        private function changeScreenPermition($data){
            $msg = (Object)[
                'type' => 'changeScreenPermition',
                'status' => $data->status
            ];
            $data->player->send( json_encode( $msg) );
        }

        private function clearScreen(){
            $this->lines = [];
            foreach ($this->clients as $client) {
                // The sender is not the receiver, send to each client connected
                $client->send( json_encode( (Object)['type'=>'clear'] ) );
            }
        }

        private function setName($from, $msg){

            foreach( $this->clients as $client ){
                if( $client->player->name === $msg->name ){
                    $result = (Object)[
                        'type' => 'InvalidName',
                        'msg' => 'Este nick já está sendo usado'
                    ];
                    $from->send( json_encode($result) );
                    return false;
                }
            }

            $newFrom = $from;
            $newFrom->player->name = $msg->name;

            $this->clients->offsetSet($from, $newFrom);

            $msg = (Object)[
                'type' => 'chat',
                'msg' => "$msg->name Entrou na sala",
                'name' => ''
            ];

            foreach( $this->clients as $client ){
                if( $client !== $from ){
                    $client->send( json_encode($msg) );
                }
            }
            
            $this->updatePlacar();
        }

        private function updatePlacar(){
            $players = [];

            foreach ($this->clients as $client) {
                array_push($players, $client->player);
            }

            foreach ($this->clients as $client) {
                $msg = (Object)[
                    'type'=>'updatePlacar',
                    'players'=> $players
                ];

                $client->send( json_encode($msg) );
            }
        }
        
        private function startNewGame(){
            if( sizeof($this->clients) > 1 && !$this->$gameStarted ){
                foreach( $this->clients as $client ){
                    $client->player->pts = 0;
                }
                $this->$gameStarted = true;
                $this->newTurn();
            } else {
                // Esperar por jogadores
            }
        }

        private function endTurn(){
            $this->turnActive = false;

            // setar timer no backend
            $this->setTimer(10);
            // pausar desenho
            // setar timer de pause
            foreach ($this->clients as $client) {    
                $screenPermition = (Object)[
                    'status' => false,
                    'player' => $client
                ];
                $this->changeScreenPermition($screenPermition);
                $this->setClientTimer($client);
            }

            // limpar desenho
            $this->clearScreen();         

            // verificar pontos
                // chamar novo turno
                
                // finalizar jogo
        }

        private function newTurn(){
            $this->turnActive = true;

            // selecionar jogador
            $this->clients->next();
            if( !$this->clients->valid() ){
                $this->clients->rewind();
            }
            $this->actualPlayer =  $this->clients->current();

            // definir tema
            $this->answer = 'batata';

            // setar timer no backend
            $this->setTimer(60);

            // bloquear screen para usuarios não pintores
            // setar timer nos clients
            foreach ($this->clients as $client) {
                $msg = (Object)[
                    'status' => false,
                    'player' => $client
                ];
                if( $client === $this->actualPlayer ){
                    $msg->status = true;
                }
                $this->changeScreenPermition($msg);
                $this->setClientTimer($client);
                echo $client->player->name;
            }
        }
    


    }