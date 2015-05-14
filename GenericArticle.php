<?php

/**
 * Generic Model class for the `jie_articles` table
 *
 * 
 * @author karthik dev
 * 
*/

class GenericArticle {

        protected $db;

	protected $article_id;
        private $article_data;
        private $article_meta;

        private $modified_flag = false;

        /**
         *
         * @global <DB Connection> $wsjdb
         * 
         */

	public function __construct(){
            global $wsjdb;
            $this->db = &$wsjdb;
            
            $this->article_id = 0;
            $this->article_data = array();
            $this->article_meta = array();
	}

        /**
         * method used to set article ID 
         * @param <int> $article_id
         */

        public function setArticleId($article_id) {
            $this->article_id = $article_id;
        }

        /**
        *method used to get article ID
        * @return <int>
        *
        */
        public function getArticleId() {
            return $this->article_id;
        }

        /**
        * method used to load an article 
        * @param <int> $article_id
        * @return <type>
        * 
        */
	public function load($article_id){
            $this->article_id = $article_id;

            $select_article_query = 'SELECT * FROM jie_articles WHERE article_id = %d LIMIT 1';

            $this->article_data = $this->db->get_row( $this->db->prepare($select_article_query, $this->article_id), ARRAY_A );
            unset($this->article_data['article_id']);   //remove the article_id so that it doesn't clash with $article_id

            if(is_null($this->article_data))
                return false;

            if($this->article_data === false)
                return false;

            $select_article_meta_query = 'SELECT * FROM jie_article_meta WHERE article_id = %d';
            $this->article_meta = $this->db->get_results( $this->db->prepare($select_article_meta_query, $this->article_id), ARRAY_A );
            if(is_null($this->article_meta))
                $this->article_meta = array();

            //Iterate the loop for checking the existence of any serialized values.
            //Unserialize if found.
            foreach ($this->article_meta as &$article_meta_item) {      //Used '&' before $article_meta_item, so the actual array can be modified
                if($this->is_serialized($article_meta_item['meta_value']))
                    $article_meta_item['meta_value'] = unserialize($article_meta_item['meta_value']);
            }
        }


        /**
        * method used to create a article if one doesn't exsist
        *
        */
        public function save(){
            //If there is no article id, it means a new article has to be inserted
            //If there is an article id, it means the existing article has to be updated
            if($this->article_id === 0 && !empty($this->article_data) ) {
                $insert_success = $this->db->insert('jie_articles', $this->article_data);
                $this->article_id = $this->db->insert_id;
                return $insert_success;
            }
            else if($this->article_id !== 0 && $this->modified_flag === true) {
                $update_success = $this->db->update('jie_articles', $this->article_data, array('article_id' => $this->article_id));
                return $update_success;
            }
    	}

        /**
         * method used to add article meta
         * @param <string> $key
         * @param <string|array> $value
         * @return <type>
         */

	public function addMeta($key, $value){
            if($this->article_id === 0)
                return false;

            //Before adding the meta, check if it exist and return false if it does
            foreach ($this->article_meta as $article_meta_item) {
                if($article_meta_item['meta_key'] === $key)
                    return false;   //Retun false if key exists
            }

            //Set the value if the loop is complete and no matching keys are found,
            //Thus it is safe to add that key. First insert into table, then add into array
            $serialized_value = is_array($value) ? serialize($value) : $value;
            $this->db->insert( 'jie_article_meta', array('article_id' => $this->article_id, 'meta_key' => $key, 'meta_value' => $serialized_value ) );  //Serialized value goes to the table
            array_push($this->article_meta,array('meta_id'=> $this->db->insert_id, 'article_id' => $this->article_id, 'meta_key' => $key, 'meta_value' => $value) ); //Add new array along with the unserialized value to the article_meta array

        }

        /**
         * method used to update the article meta
         * @param <string> $key
         * @param <string|array> $value
         * @return <type>
         *
         */

	public function updateMeta($key, $value){
            if($this->article_id === 0)
                return false;

            //Iterate to find out the row with the matching key.
            //If a matching row is found, update it. First update the table, then update the array
            foreach ($this->article_meta as &$article_meta_item) {    //Extracting the individual element as a reference(&) to directly modify it.

                if($article_meta_item['meta_key'] === $key)
                {
                    $serialized_value = is_array($value) ? serialize($value) : $value;
                    $this->db->update( 'jie_article_meta', array('meta_value' => $serialized_value) , array('meta_id' => $article_meta_item['meta_id']) ); //Serialized value goes to the table
                    $article_meta_item['meta_value'] = $value;    //Set the value, if a matching key is found. Set the unserialized value as you are adding this to the array
                    return true;
                }
            }
            return false;
        }

        /**
         * method used to retrieve the value of the article meta
         * @param <string> $key
         * @return <type>
         *
         */

        public function getMeta($key){
            if($this->article_id === 0)
                return false;

            //Iterate to find out the row with the matching key.
            //If a matching row is found, unserialize and return it.
            foreach ($this->article_meta as $article_meta_item) {
                if($article_meta_item['meta_key'] === $key)
                    return $article_meta_item['meta_value'];
            }

            return '';
        }

        /**
         * method used to delete article meta
         * @param <string> $key
         * @return <type>
         *
         */

        public function deleteMeta($key){
            if($this->article_id === 0)
                return false;

            //Iterate to find out the row with the matching key.
            //If a matching row is found, delete it. First delete from table, then remove from array
            foreach ($this->article_meta as $article_meta_item)
            {
                if($article_meta_item['meta_key'] === $key)
                {
                    $delete_query = ' DELETE FROM jie_article_meta WHERE meta_id = %d ' ;
                    $this->db->query( $this->db->prepare($delete_query, $article_meta_item['meta_id'] ));
                    unset($article_meta_item);
                    return true;
                }
            }
            return false;
        }

        /**
         *method used to get a value after instantiatiing the generic article class
         * @param <string> $key
         * @return <type>
         *
         */

	public function get($key){
            return $this->article_data[$key];
	}

        /**
         * method used to set a value after instantiatiing the generic article class
         * @param <string> $key
         * @param <string> $value
         * @return <type>
         */

	public function set($key, $value){
            if($key === 'article_id')
                return false;
            $this->article_data[$key] = $value;
            $this->modified_flag = true;
	}

        /**
         * Checks whether the supplied data is seralized. Returns true or false.
         *
         * @param <string> $data
         * @return <type>
         * 
         */

        private function is_serialized( $data ){
            // if it isn't a string, it isn't serialized
            if ( !is_string( $data ) )
                return false;
            $data = trim( $data );
            if ( 'N;' == $data )
                return true;
            if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
                return false;
            switch ( $badions[1] ) {
                case 'a' :
                case 'O' :
                case 's' :
                        if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                                return true;
                        break;
                case 'b' :
                case 'i' :
                case 'd' :
                        if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                                return true;
                        break;
            }
            return false;
        }
}

?>
