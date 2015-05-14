<?php

/**
 * Model class for the `jie_articles` table for article_type='lesson'
 *
 * @author Karthik Dev
 * 
 * 
*/

require_once( WSJ::$model_path.'/GenericArticle.php' );

class Lesson extends GenericArticle {

    private $article_ids;
    private $folder_ids;

    /**
     *
     */

    function __construct(){

        parent::__construct();
        //Set the article_type to be 'lesson'
        $this->set('article_type', 'lesson');

        $this->articles_ids = array();
        $this->folder_ids = array();
    }

    /**
     *
     * @param <type> $article_ids
     */

    public function loadArticleIDsFromArray($article_ids) {

        $this->article_ids = $article_ids;
    }

    /**
     *
     * @param <type> $folder_ids
     */

    public function loadFolderIDsFromArray($folder_ids) {
        
        $this->folder_ids = $folder_ids;
    }

    /**
     *
     * @param <type> $lesson_id
     *
     */

    public function delete($lesson_id) {

        parent::delete($article_id);

        //Remove the lesson from the folders, where it is grouped
        $delete_relationships_query = ' DELETE FROM jie_lesson_folder WHERE lesson_id = %d ' ;
        $delete_relationships_success = $this->db->query( $this->db->prepare($delete_relationships_query, $lesson_id ) );
    }
     
    /**
     * Adds The Article to the Lesson
     *
     * @param <int> $article_id
     * 
     */

    public function addArticle($article_id){
        $this->setArticles(array($article_id), true);
    }

    /**
     * Method to add or update article set for a given Lesson
     *
     * @param <array> $article_ids
     * @param <boolean> $append
     * 
     */

    public function setArticles($article_ids, $append = false){

        if($append === true){

            $existing_articles_array = array();

            $select_articles_in_lesson_query = "SELECT * FROM jie_article_lesson WHERE lesson_id = %d";
            $results  = $this->db->get_results( $this->db->prepare($select_articles_in_lesson_query, $this->article_id), ARRAY_A ); //we are passing article_id to lesson_id because when we are in the lesson class, the variable article_id refers to the lesson_id

            if(is_null($results))
                $results = array();

            foreach ($results as $row) {
                array_push($existing_articles_array, $row['article_id']);
            }

            foreach($article_ids as $article_id) {
                if(in_array($article_id, $existing_articles_array) )
                    continue;
                $this->db->insert('jie_article_lesson',
                                    array( 'lesson_id' => $this->article_id,    //$this->article_id points to lesson id, $article_id is the article_id to be added
                                           'article_id' => $article_id,
                                            'position' => (count($this->article_ids) + $array_position + 1) ));
                array_push($this->article_ids, $article_id);
            }

        }
        else if($append === false){

            $delete_articles_in_lesson_query = "DELETE FROM jie_article_lesson WHERE lesson_id = %d";
            $delete_success = $this->db->query($this->db->prepare($delete_articles_in_lesson_query, $this->article_id)); //we are passing article_id to lesson_id because when we are in the lesson class, the variable article_id refers to the lesson_id

            foreach($article_ids as $array_position=>$article_id) {
                $this->db->insert('jie_article_lesson',
                                    array( 'lesson_id' => $this->article_id,    //$this->article_id points to lesson id, $article_id is the article_id to be added
                                           'article_id' => $article_id,
                                           'position' => ($array_position + 1) )
                                         );
            }
         
            $this->article_ids = article_ids;
         }
         
    }

    /**
     *
     */

    public function getArticles(){

        require_once( WSJ::$model_path.'/ArticleFactory.php' );

        $articleFactory = new ArticleFactory();

        return $articleFactory->getArticlesByLessonID($this->article_id);

    }

    /**
     *
     * @return <type>
     *
     */

    public function getArticleIDs(){

        return $this->article_ids;
    }

    /**
     * Get the list Of Articles in the Lesson
     *
     * @return <type>
     */

    public function updateArticleIDs(){
        //return $this->getMeta('articles');

        $select_articles_in_lesson_query = "SELECT jie_article_lesson.article_id FROM jie_article_lesson, jie_articles
                                            WHERE
                                                jie_article_lesson.article_id = jie_articles.article_id
                                                AND jie_articles.article_type = 'article'
                                                AND jie_articles.article_status = 'publish'
                                                AND jie_article_lesson.lesson_id = %d
                                            ORDER BY
                                                jie_article_lesson.position";
        $results  = $this->db->get_results( $this->db->prepare($select_articles_in_lesson_query, $this->article_id), ARRAY_A ); //we are passing article_id to lesson_id because when we are in the lesson class, the variable article_id refers to the lesson_id

        if(is_null($results))
            $results = array();

        $existing_articles_ids_array = array();

        //print_r($results);
        
        foreach ($results as $row) {
            $existing_articles_ids_array[] = $row['article_id'];
        }

        $this->article_ids = $existing_articles_ids_array;
    }
    
    /**
     *
     * @param <type> $folder_id 
     * 
     */
    
    public function addFolder($folder_id){

        setFolders(array($folder_id), true);
    }

    /**
     *
     * @param <type> $folder_ids
     * @param <type> $append
     *
     */

    public function setFolders($folder_ids, $append = false){

        if($append === true) {

            $existing_folders_array = array();

            $select_folders_for_lesson_query = "SELECT * FROM jie_lesson_folder WHERE lesson_id = %d";
            $results  = $this->db->get_results( $this->db->prepare($select_folders_for_lesson_query, $this->article_id), ARRAY_A ); //we are passing article_id to lesson_id because when we are in the lesson class, the variable article_id refers to the lesson_id

            if(is_null($results))
                $results = array();

            foreach ($results as $row) {
                array_push($existing_folders_array, $row['folder_id']);
            }

            foreach($folder_ids as $folder_id) {
                if(in_array(folder_id, $existing_folders_array) )
                    continue;
                $this->db->insert('jie_lesson_folder', 
                                    array( 'lesson_id' => $this->article_id,    //$this->article_id points to lesson id, $article_id is the article_id to be added
                                           'folder_id' => $folder_id ));
                array_push($this->folder_ids, $folder_id);
            }
         }
         else if($append === false){

            $delete_folders_for_lesson_query = "DELETE FROM jie_lesson_folder WHERE lesson_id = %d";
            $delete_success = $this->db->query($this->db->prepare($delete_folders_for_lesson_query, $this->article_id)); //we are passing article_id to lesson_id because when we are in the lesson class, the variable article_id refers to the lesson_id

            foreach($folder_ids as $folder_id) {
                $this->db->insert('jie_lesson_folder', 
                                    array( 'lesson_id' => $this->article_id,    //$this->article_id points to lesson id, $article_id is the article_id to be added
                                           'folder_id' => $folder_id ));
            }

            $this->folder_ids = $folder_ids;
         }
    }

    /**
     *
     */

    public function getFolderIDs(){
       return $this->folder_ids;
    }

    /**
     * 
     */

    public function updateFolderIDs(){

        $select_folders_of_lesson_query = "SELECT * FROM jie_lesson_folder WHERE lesson_id = %d";
        $results  = $this->db->get_results( $this->db->prepare($select_folders_of_lesson_query, $this->article_id), ARRAY_A ); //we are passing article_id to lesson_id because when we are in the lesson class, the variable article_id refers to the lesson_id

        if(is_null($results))
            $results = array();

        $existing_folder_ids_array = array();

        foreach ($results as $row) {
            $existing_folder_ids_array[] = $row['folder_id'];
        }

        $this->folder_ids = $existing_folder_ids_array;
    }

    public function remove_lesson_folder($folder_id){

        $delete_lesson_folder_query = 'DELETE FROM jie_lesson_folder WHERE lesson_id = %d AND folder_id= %d';
        $delete_success = $this->db->query( $this->db->prepare($delete_lesson_folder_query, $this->article_id, $folder_id) );

        $this->updateFolderIDs();

        $select_lesson_folder_query = 'SELECT count(*) FROM jie_lesson_folder WHERE lesson_id = %d';
        $rows = $this->db->get_var( $this->db->prepare($select_lesson_folder_query, $this->article_id) );

        if( (int)$rows === 0 )
        {
            $this->set('article_status', 'draft');
            $this->set('article_publish_date', '0000-00-00 00:00:00');
            $this->save();
        }

        return $delete_success;
    }


    /**
     *
     * @param <type> $article_id
     * @param <type> $article_status_req
     *
     */

    public function changeLessonStatus( $lesson_status_requested ) {

        global $current_user;

            if(  ($this->get('user_id') !== $current_user->getID() )
                    && $current_user->getRole() !== 'admin')
            return -1;

        switch ($lesson_status_requested)
        {
            case 'deleted':
                    if( $this->get('article_status') !== 'deleted')
                    {
                        $this->set('article_modified_date', date('Y-m-d H:i:s'));
                        $this->set('article_status', $lesson_status_requested);
                        return $this->save();
                    }
                    return 0;
                    break;
            case 'draft':
                    if( $this->get('article_status') !== 'draft')
                    {
                        if($this->get('article_status') === 'publish' || $this->get('article_status') === 'future')
                            $this->set('article_publish_date', '0000-00-00 00:00:00');  //Considering the case for Undo Publish
                        $this->set('article_modified_date', date('Y-m-d H:i:s'));
                        $this->set('article_status', $lesson_status_requested);
                        return $this->save();
                    }
                    return 0;
                    break;
            case 'future':
                    if( $this->get('article_status') !== 'future')
                    {
                        $this->set('article_publish_date', date('Y-m-d H:i:s'));
                        $this->set('article_modified_date', date('Y-m-d H:i:s'));
                        $this->set('article_status', $lesson_status_requested);
                        return $this->save();
                    }
                    return 0;
                    break;
            case 'publish':
                    if( $this->get('article_status') !== 'publish')
                    {
                        $this->set('article_publish_date', date('Y-m-d H:i:s'));
                        $this->set('article_modified_date', date('Y-m-d H:i:s'));
                        $this->set('article_status', $lesson_status_requested);
                        return $this->save();
                    }
                    return 0;
                    break;

        }

    }

}

?>
