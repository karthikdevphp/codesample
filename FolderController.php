<?php

/**
 * Controller class for managing the lesson
 *
 *
 * @author Karthik Dev
 *
*/

 class FolderController extends ApplicationController
 {
    
    /*
    *Index controller the default controller 
    */
    public function index() {
        print "Folder Controller";
        return;
    }

    /**
    * method adds the folder
    * @global <type> $current_user
    * @param <type> $folder_name
    *
    */
    public function add( $folder_name ) {

        global $current_user;

        if ( ! $this->checkPermission('professor', 'create_folder' ) ){
            print json_encode(array('status'=> -1, 'msg'=> 'The user does not have the permission for the requested operation' ));
            return;
        }

    	require_once( WSJ::$model_path.'/Folder.php');

        $folder = new Folder();
    	$folder->set('folder_name',$folder_name);
        $folder->set('user_id', $current_user->getID());
    	$insert_success = $folder->save();

        print json_encode(array('status' => $insert_success, 'msg' => '', 'folder_id' => $folder->getFolderId()) );
      
    }

    /**
     * method deletes the folder
     * @global <type> $current_user
     * @param <type> $folder_id
     *
     */
    public function delete( $folder_id ) {
    	global $current_user;

        if ( ! $this->checkPermission('professor', 'delete_folder' ) ){
            print json_encode(array('status'=> -1, 'msg'=> 'The user does not have the permission for the requested operation' ));
            return;
        }
        
        require_once( WSJ::$model_path.'/Folder.php');

        $folder = new Folder();
    	$folder->load($folder_id);
    	$delete_success = $folder->delete();

        print json_encode(array('status' => $delete_success, 'msg' => '' ));
    }

    /**
     * method remanes the folder
     * @global <type> $current_user
     * @param <type> $folder_id
     * @param <type> $folder_new_name
     *
     */
    public function rename( $folder_id, $folder_new_name ) {
     	global $current_user;

        if ( ! $this->checkPermission('professor', 'create_folder' ) ){
            print json_encode(array('status'=> -1, 'msg'=> 'The user does not have the permission for the requested operation' ));
            return;
        }
        
        require_once( WSJ::$model_path.'/Folder.php');

        $folder = new Folder();
     	$folder->load($folder_id);
     	$folder->set('folder_name',$folder_new_name);
     	$update_success = $folder->save();

        print json_encode(array('status' => $update_success, 'msg' => '' ));

    }

    /**
     * checks the permission of the current user
     * @global <type> $current_user
     * @param <type> $role
     * @param <type> $permission
     *
     */
    protected function checkPermission( $role, $permission){

        global $current_user;

        if( $current_user->getRole() === 'admin' )
            return true;

        if( $current_user->getRole() === $role && $current_user->userCan( $permission ) )
            return true;

        return false;
    }
    
    /**
    * method retrives the list of all folders
    */
    public function folders(){

        global $current_user;

        $current_tab = 'lesson_builder';

        require_once( WSJ::$model_path.'/FolderFactory.php' );
        $folderFactory = new FolderFactory();
        $user_folders = $folderFactory->getFolders($current_user->getID());

        if($user_folders === false){
            print json_encode( array('status' => 0, 'msg' => 'No folders found') );
            return;
        }

        $selected_folders_array = array();

        foreach($user_folders as $user_folder){
            $selected_folders_array[] = array( 'folder_id' => $user_folder->getFolderId(), 'folder_name' => $user_folder->get('folder_name'), 'folder_slug' => $user_folder->get('folder_slug') );
        }

        print json_encode($selected_folders_array);
    }

    /**
     * method removes the lesson
     *
     * @param <type> $folder_id
     * @param <type> $lesson_id
     *
     */
    public function lesson_remove($folder_id, $lesson_id){
        
    	global $current_user;

        if ( ! $this->checkPermission('professor', 'delete_lesson' ) ){
            print json_encode(array('status'=> -1, 'msg'=> 'The user does not have the permission for the requested operation' ));
            return;
        }

        require_once( WSJ::$model_path.'/Lesson.php');

        $lesson = new Lesson();
    	$lesson->load($lesson_id);
        $lesson->updateFolderIDs();
    	$delete_success = $lesson->remove_lesson_folder($folder_id);

        print json_encode(array('status' => $delete_success, 'msg' => '' ));
        
    }
    
 }
 
 ?>
