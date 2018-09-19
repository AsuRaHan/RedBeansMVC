<?php defined('ROOT') OR die('No direct script access.');

/**
 * Description of IndexController
 *
 * @author ivank
 */
class IndexController extends Controller{
    private $TPL;
    
    public function __construct() {
        parent::__construct();
//        $path = $this->Config()['App_Templates_Dir'];
        $this->TPL = new Template();
//        $this->TPL->SetWivePath($path.__CLASS__.DS);
    }
    public function ActionIndex() {
        $TPL = $this->TPL;
        $TPL->title = "Главная страница";
        $TPL->SetWivePath($this->Config()['App_Templates_Dir'].'Posts'.DS);
        $TPL->content =  $TPL->execute('PostList.html');
        return $this->Generate($TPL);
    }
    public function ActionPost($postId) {
        $TPL = $this->TPL;
        
        $TPL->title = "Post $postId";
        $TPL->AppPostId = $postId;
        $TPL->SetWivePath($this->Config()['App_Templates_Dir'] . 'Posts' . DS);
        $TPL->content = $TPL->execute('Post.html');
        return $this->Generate($TPL);
    }
    
    public function ActionApi($param) {
        echo 'OK';
    }
           
}
