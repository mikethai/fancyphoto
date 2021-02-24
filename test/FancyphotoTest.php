<?php
use mikecai\fancyphoto\Fancyphoto;

class FancyphotoTest extends PHPUnit_Framework_TestCase
{

//    function test(){
//
//        $db_host = $_ENV['DB_HOST'];
//        $db_user = $_ENV['DB_USER'];
//        $db_password = $_ENV['DB_PASSWD'];
//        $db_name = $_ENV['DB_DBNAME'];
//        $upload_folder = $_ENV['UPLOAD_FOLDER'];
//
//
//        $fancyphoto = new Fancyphoto($db_host,$db_user,$db_password,$db_name,$upload_folder);
//
//        $this->assertInstanceOf(mikecai\fancyphoto\Fancyphoto,$fancyphoto);
//    }

    function test_judge_monster_level(){

        $level1 = $this->judge_monster_level(0);
        $level2 = $this->judge_monster_level(1);
        $level3 = $this->judge_monster_level(5);
        $level10 = $this->judge_monster_level(26);
        $level11 = $this->judge_monster_level(279);


        $this->assertEquals(1,$level1);
        $this->assertEquals(2,$level2);
        $this->assertEquals(4,$level3);
        $this->assertEquals(10,$level10);
        $this->assertEquals(10,$level11);
    }


    function judge_monster_level($borrow_book_num){
        $level_array = array(0,1,3,5,8,11,15,19,21,26);

        if($borrow_book_num==0){
            return 1;
        }

        if($borrow_book_num>=27){
            return 10;
        }

        foreach ($level_array as $key => $value) {

            if ($borrow_book_num == $value) {
                return $key+1;
            }else if ($borrow_book_num < $value) {
                return $key;
            }
        }
    }
}
