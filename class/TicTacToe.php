<?php
    /**
     * @author Morris Jencen O. Chavez <macinville@macinville.com>
     * Class file TicTacToe  August 25, 2013
     */

    class TicTacToe {

            //character for X and O
            const X_MARK = 'X';
            const O_MARK = 'O';

            //quit button. should not be the same with X_MARK and O_MARK
            const QUIT_BUTTON = 'Q';

            //board size
            protected $_boardSize;

            //all possible winning combination
            protected $_winningCombination;

            //will contain the markers of both side
            protected $_markers;

            //contains the coordinates of the cells
            protected $_boardResponse;

            //an already opened stream
            protected $_stdin;

            //marker selected by the user
            protected $_userMark;

            //marker not selected by the user
            protected $_botMark;

            //centralized handler of prompts
            protected $_prompts;

            /**
             * Public constructor
             */
            public function __construct() {
                    //initialize the values of the fixed variables
                    $this->_boardSize = array(3, 3);
                    $this->_winningCombination = array(
                        '1,3,2',
                        '5,4,6',
                        '7,9,8',
                        '1,7,4',
                        '5,2,8',
                        '3,9,6',
                        '5,1,9',
                        '5,3,7',
                    );
                    $this->_boardResponse = array(
                        '1' => '11',
                        '2' => '12',
                        '3' => '13',
                        '4' => '21',
                        '5' => '22',
                        '6' => '23',
                        '7' => '31',
                        '8' => '32',
                        '9' => '33',
                    );
                    $this->_boardResponse[self::QUIT_BUTTON] = self::QUIT_BUTTON;
                    $this->_stdin = STDIN;
                    $this->_prompts = array(
                        'choose_mark' => "Choose your marker (" . self::X_MARK . " or " . self::O_MARK . "): ",
                        'start_game' => "To play, press a number representing the matrix above. ",
                        'invalid_input' => "Invalid input. Please try again: ",
                        'invalid_move' => "Invalid move. Please try again: ",
                        'reset' => "Would you like to reset the board? (yes or no): ",
                        'board_full' => "No more possible moves, board is full. This game is draw.",
                        'yesorno' => "Please answer only with yes(y) or no(n).",
                        'bye' => "Thank you for playing.",
                    );
            }

            /**
             * Runs the class file
             */
            public function run() {
                    $this->init();
                    $this->welcome();
                    $this->setMark();
                    $this->startGame();
            }

            /**
             * Initializes the board and markers
             */
            protected function init() {
                    $this->_markers = array(self::X_MARK => array(), self::O_MARK => array());
            }

            /*
             * Displays welcome message and draws the initial board
             */
            protected function welcome() {
                    echo "*****************" . PHP_EOL .
                    "*** TicTacToe ***" . PHP_EOL .
                    "*****************" . PHP_EOL;
                    $this->drawBoard(array(), true);
                    echo "[" . self::QUIT_BUTTON . "] - End game" . PHP_EOL . PHP_EOL;
            }

            /**
             * Assigns mark for the user and the computer
             */
            protected function setMark() {
                    echo $this->promptMessage('choose_mark');
                    $mark = $this->limitInput(array(self::X_MARK, self::O_MARK, self::QUIT_BUTTON));
                    switch ($mark) {
                            case self::X_MARK:
                                    $this->_userMark = self::X_MARK;
                                    $this->_botMark = self::O_MARK;
                                    break;
                            case self::O_MARK:
                                    $this->_userMark = self::O_MARK;
                                    $this->_botMark = self::X_MARK;
                                    break;
                            case self::QUIT_BUTTON:
                                    $this->quit();
                    }
                    echo "You will be Player " . $this->_userMark . "." . PHP_EOL;
                    echo $this->promptMessage('start_game') . PHP_EOL;
            }

            /**
             * Game proper
             */
            protected function startGame() {
                    while (TRUE) {
                            if ($this->userMove())
                                    break;
                            if ($this->AIMove())
                                    break;
                    }
                    $this->resetGame();
                    exit;
            }

            /**
             * Handles the user's turn
             * @return boolean TRUE if the game should end;FALSE if the game should continue
             */
            protected function userMove() {
                    echo "Your move: ";
                    $move = FALSE;
                    while ($move === FALSE) {
                            $move = $this->isValidMove();
                            if ($move === FALSE)
                                    echo $this->promptMessage('invalid_move');
                    }
                    if (strtoupper($move) == self::QUIT_BUTTON)
                            return TRUE;


                    echo "Your move is '" . $this->_userMark . "' at box " . $move . PHP_EOL;
                    $this->_markers[$this->_userMark][] = $move;
                    $this->drawBoard($this->_markers);
                    if ($this->isWon($this->_userMark) || $this->isBoardFull())
                            return TRUE;
                    return FALSE;
            }

            /**
             * Handles the computer's turn, draws the board and displays it to the user
             * @return boolean TRUE if the game should end;FALSE if the game should continue
             */
            protected function AIMove() {
                    $botMove = $this->botMove();
                    echo "Computer's move is '" . $this->_botMark . "' at box " . $botMove . PHP_EOL;
                    $this->_markers[$this->_botMark][] = $botMove;
                    $this->drawBoard($this->_markers);
                    if ($this->isWon($this->_botMark) || $this->isBoardFull())
                            return true;
                    return false;
            }

            /**
             * Displays the board
             * @param array $markers    the marks that should be placed on the board
             * @param bool $showBoxNo   whether to show the box number or not. Only set to true on start
             */
            protected function drawBoard($markers = array(), $showBoxNo = false) {
                    $maxRows = $this->_boardSize[0];
                    $maxCols = $this->_boardSize[1];
                    $boxCtr = 0;

                    $X = array();
                    $O = array();
                    if ($markers !== FALSE && !$showBoxNo) {
                            if (isset($markers[self::X_MARK]))
                                    foreach ($markers[self::X_MARK] AS $xMarks)
                                            $X[] = $this->_boardResponse[$xMarks];
                            if (isset($markers[self::O_MARK]))
                                    foreach ($markers[self::O_MARK] AS $oMarks)
                                            $O[] = $this->_boardResponse[$oMarks];
                    }
                    echo PHP_EOL;
                    for ($row = 1; $row <= $maxRows; $row++) {
                            for ($col = 1; $col <= $maxCols; $col++) {
                                    ++$boxCtr;
                                    if ($markers != array() && in_array("$row$col", $X))
                                            $content = self::X_MARK;
                                    elseif ($markers != array() && in_array("$row$col", $O))
                                            $content = self::O_MARK;
                                    else
                                            $content = $showBoxNo ? $boxCtr : ' ';
                                    echo ($col < $maxCols) ? $content . ' |' : $content;
                            }
                            echo ($row < $maxRows) ? PHP_EOL . '--------' . PHP_EOL : '';
                    }
                    echo PHP_EOL . PHP_EOL;
            }

            /**
             * Checks the board if someone wone the game
             * @param string $marker 	either X_MARK or O_MARK
             * @return boolean 			TRUE if there's a winner;FALSE if none
             */
            protected function isWon($marker) {
                    $markedBox = $this->_markers[$marker];
                    foreach ($this->_winningCombination AS $box) {
                            $lineBoxes = explode(",", $box);        //separate the winning combination to 3
                            if (in_array($lineBoxes[0], $markedBox) && in_array($lineBoxes[1], $markedBox) && in_array($lineBoxes[2], $markedBox)) {
                                    echo "Player $marker won!" . PHP_EOL;
                                    return TRUE;
                            }
                    }
                    return FALSE;
            }

            /**
             * Checks if the move to be drawn on the board is valid
             * @param string $move 	if set, it will call isCellOpen to validate if the cell is not yet occupied
             *                          and belongs to $_boardResponse
             * @return boolean 		TRUE if the move is valid;FALSE if not
             */
            protected function isValidMove($move = NULL) {
                    $hasValidMove = FALSE;
                    if (isset($move)) {
                            if (isset($this->_boardResponse[$move]) && $this->isCellOpen($move))
                                    return $move;
                            return false;
                    } else {
                            while (TRUE) {
                                    $move = strtoupper($this->userInput());
                                    if (isset($this->_boardResponse[$move]) && $this->isCellOpen($move))
                                            return $move;
                                    return false;
                            }
                    }
            }

            /**
             * Checks whether the cell is occupied or not
             * @param int $cell 	cell number to be checked
             * @return boolean 		TRUE if the cell is available;FALSE if not
             */
            protected function isCellOpen($cell) {
                    return (!in_array($cell, $this->_markers[self::X_MARK]) AND !in_array($cell, $this->_markers[self::O_MARK]));
            }

            /**
             * Checks whether the board has no more possible moves
             * @return boolean TRUE if the board is full;FALSE if not
             */
            protected function isBoardFull() {
                    if ((count($this->_markers[self::X_MARK]) + count($this->_markers[self::O_MARK])) == 9) {
                            echo $this->promptMessage('board_full') . PHP_EOL;
                            return TRUE;
                    }
                    return FALSE;
            }

            /**
             * Asks the user if he/she wants to reset the game.
             */
            protected function resetGame() {
                    $feedback = NULL;
                    while (!is_bool($feedback)) {
                            echo $this->promptMessage('reset');
                            $response = $this->userInput();
                            $feedback = $this->simpleAnswer($response);
                            if ($feedback === TRUE)
                                    $this->run();
                            elseif ($feedback === FALSE) {
                                    $this->quit();
                            }
                            else
                                    echo $feedback;
                    }
            }

            /**
             * Handles the computer's turn
             * @return integer  the cell number of the computer's turn
             */
            protected function botMove() {
                    $key = $this->thinkBot();
                    return $key;
            }

            /**
             * The logic of computer's turn
             * @return integer  the evaluated cell number that should be marked by the computer
             */
            protected function thinkBot() {
                    $cellReturn = '';
                    $markers = array($this->_botMark, $this->_userMark);  //offense first, then defense
                    while ($this->isValidMove($cellReturn) === FALSE) {
                            //defense and offense
                            foreach ($markers AS $marker) {
                                    foreach ($this->_winningCombination AS $cells) {
                                            $lineCells = explode(",", $cells);        //separate the winning combination to 3
                                            $marked = 0;
                                            $noMark = 0;
                                            for ($x = 0; $x < 3; $x++) {
                                                    if (in_array($lineCells[$x], $this->_markers[$marker]))
                                                            $marked++;
                                                    else
                                                            $noMark = $lineCells[$x];
                                            }
                                            if ($marked == 2 && $this->isValidMove($noMark)) {
                                                    $cellReturn = $noMark;
                                                    break;
                                            }
                                    }
                                    if ($cellReturn != '')
                                            break;
                            }
                            //plan
                            if ($cellReturn == '') {
                                    while (!$this->isValidMove($cellReturn)) {
                                            $combinationRandKey = rand(0, 7);
                                            //separate the winning combination to 3 elements
                                            $lineCells = explode(",", $this->_winningCombination[$combinationRandKey]);
                                            if ($this->isValidMove($lineCells[0]))
                                                    $cellReturn = $lineCells[0];
                                            elseif ($this->isValidMove($lineCells[1]))
                                                    $cellReturn = $lineCells[1];
                                            else
                                                    $cellReturn = $lineCells[2];
                                    }
                            }
                    }
                    return $cellReturn;
            }

            /**
             * Handles the answers to a yes or no question
             * @param string $response 	the user input
             * @return mixed 			TRUE if the user chose yes,FALSE if no, and the error message if the answer is neither y or no
             */
            protected function simpleAnswer($response) {
                    $response = strtoupper($response);

                    if ($response === 'Y' || $response === 'YES')
                            return TRUE;
                    else if ($response === 'N' || $response === 'NO')
                            return FALSE;
                    else
                            return $this->promptMessage('yesorno') . PHP_EOL;
            }

            /**
             * Limits the input to the allowed answers
             * @param type $allowedValues   contains the valid answers
             * @return string   			the selected valid answer
             */
            protected function limitInput($allowedValues) {
                    $input = NULL;
                    while (!in_array($input, $allowedValues)) {
                            $input = strtoupper($this->userInput());
                            if (!in_array($input, $allowedValues))
                                    echo $this->promptMessage('invalid_input');
                    }
                    return $input;
            }

            /**
             * Handles the user's input
             * @return mixed    the user input
             */
            protected function userInput() {
                    return trim(fgets($this->_stdin));
            }

            /**
             * Handles the prompting of messages
             * @param string $index 	the type of message to prompt
             * @return string 			the message to be prompted
             */
            protected function promptMessage($index) {
                    return $this->_prompts[$index];
            }

            /**
             * Closes the game after a farewell message
             */
            protected function quit() {
                    echo $this->promptMessage('bye') . PHP_EOL . PHP_EOL;
                    exit;
            }
    }