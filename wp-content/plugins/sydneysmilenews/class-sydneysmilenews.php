<?php

class SydneySmileNews {

    protected $_error = array();
    protected $_jsonData = null;
    protected $_data = array();

    protected $_base = 100000000; //convert bigint ids to int ids for 32 bit processor
    protected $_batch = 10; //process queries in batch,default to 1000, and take a break in between to reduce to the load on the database server
    protected $_sleepTime = 1; //time to sleep between batches, default to 1 second

    public function setError($msg) {
        if(is_array($msg)) {
            foreach($msg as $m) {
                $this->_error[] = $m;
            }
        } else {
            $this->_error[] = $msg;
        }
    }

    public function getError() {
        return $this->_error;
    }

    public function setJsonData($jsonData) {
        $this->_jsonData = $jsonData;
    }

    public function getData() {
        return $this->_data;
    }

    public function parseJsonData($json=null) {
        if($json != null) {
            $this->_jsonData = $json;
        }

        if(empty($this->_jsonData)) {
            $this->setError('No Json data found');
            return false;
        }

        $this->_data = $this->getDataFromJson($this->_jsonData);
        if($this->_data == false) {
            $this->setError('Invalid Json data');
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $str
     * @return array|bool|mixed
     * get the data by decoding the Json string
     */
    public function getDataFromJson($str) {
        $data = json_decode($str);
        if(json_last_error() == JSON_ERROR_NONE) {
            return $data;
        } else {
            $this->setError('failed to decode json data '.$str);
            return false;
        }
    }

    /**
     * @return bool
     * populate the database tables with the data from the decoded Json string
     */
    public function populatePostData() {
        if(empty($this->_data->data) || !is_array($this->_data->data)) {
            $this->setError('No data to process');
            return false;
        }

        date_default_timezone_set('Australia/Sydney');  //set the local time zone

        $siteUrl = get_site_url();
        $idLength = strlen($this->_base);

        $x = 0;
        foreach ($this->_data->data as $post) {
            $p = new stdClass();  //post detail
            $p->content = new stdClass();
            $u = new stdClass();  //author detail

            if(!empty($post->id)) {
                $a = explode('_', $post->id);
                $p->post_author = intval(substr($a[0], -$idLength));
                $p->ID = intval(substr($a[1], -$idLength));
                $p->guid = $siteUrl."/?p=".$p->ID;
            }

            if(!empty($post->from)) {
                $from = $post->from;
                $u->catetory = $from->category;
                $u->ID = intval(substr($from->id, -$idLength));
                $u->user_login = $from->name;
            }

            if(!empty($post->type)) {
                $p->post_type = $post->type;
            }

            if(!empty($post->message)) {
                $p->post_title = $post->message;
            }

            if(!empty($post->picture)) {
                $a = explode('&',urldecode($post->picture));
                foreach($a as $v) {
                    if(substr($v, 0, 3) == 'url') {
                        $p->content->picture = substr($v, 4);
                    }
                };
            }

            if(!empty($post->link)) {
                $p->content->link = $post->link;
            }

            if(!empty($post->name)) {
                $p->content->name = $post->name;
                $p->post_name = implode('-', explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', $post->name)));
            } elseif ($post->type == 'video') {
                $p->content->name = $post->message;
                $p->post_name = implode('-', explode(' ', preg_replace('/[^a-zA-Z0-9 ]/', '', $post->message)));
            } else {
                $p->post_name = '';
            }

            if(!empty($post->caption)) {
                $p->content->caption = $post->caption;
            }

            if(!empty($post->created_time)) {
                $time = strtotime($post->created_time);
                $p->post_date = date('Y-m-d H:i:s', $time);
                $p->post_date_gmt = gmdate('Y-m-d H:i:s', $time);
            }

            if(!empty($post->updated_time)) {
                $time = strtotime($post->updated_time);
                $p->post_modified = date('Y-m-d H:i:s', $time);
                $p->post_modified_gmt = gmdate('Y-m-d H:i:s', $time);
            }

            if(!empty($post->comments) && !empty($post->comments->data)) {
                $p->comment_count = sizeof($post->comments->data);
                foreach($post->comments->data as $k => $comment) {
                    $c = new stdClass();
                    if(!empty($comment->id)) {
                        $a = explode('_', $comment->id);
                        $c->comment_post_ID = intval(substr($a[0], -$idLength));
                        $c->comment_ID = intval(substr($a[1], -$idLength));
                    }

                    if(!empty($comment->from) && !empty($comment->from->name)) {
                        $c->comment_author = $comment->from->name;
                        $c->user_id = intval(substr($comment->from->id, -$idLength));
                    }

                    if(!empty($comment->message)) {
                        $c->comment_content = $comment->message;
                    }

                    if(!empty($comment->created_time)) {
                        $time = strtotime($comment->created_time);
                        $c->comment_date = date('Y-m-d H:i:s', $time);
                        $c->comment_date_gmt = gmdate('Y-m-d H:i:s', $time);
                    }

                    $this->_saveComment($c);
                }
            }

            $this->_savePost($p);
            $this->_saveUser($u);

            $x++;
            if ($x % $this->_batch == 0) {
                echo "sleep ".$this->_sleepTime." seconds after ". $x ." records...";
                sleep(1);
                echo "ok <br />\n";
            }
        }
        return true;
    }


    protected function _saveUser($user) {
        global $wpdb;
        $id = $user->ID;
        $user_login = $user->user_login;
        if($this->_isUserExisting($id)) {
            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->users} SET user_login = %s WHERE ID = %d", $user_login, $id ) );
        } else {
            $wpdb->query( $wpdb->prepare( "INSERT INTO `$wpdb->users` (`ID`, `user_login`, `user_nicename`, `display_name`) VALUES (%s, %s, %s, %s)", $id, $user_login, $user_login, $user_login ) );

            if(!$this->_isUserMetaExisting($id)) {
                $wpdb->query( $wpdb->prepare( "INSERT INTO `$wpdb->usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES (%s, %s, %s)", $id, 'nickname', $user_login ) );
            }
        }
    }

    protected function _isUserExisting($userId) {
        global $wpdb;
        if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE ID = %s", $userId ) ) ) {
            return true;
        } else {
            return false;
        }
    }

    protected function _isUserMetaExisting($userId) {
        global $wpdb;
        if ( $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE user_id = %s LIMIT 1", $userId ) ) ) {
            return true;
        } else {
            return false;
        }
    }

    protected function _savePost($p) {
        global $wpdb;
        $id = $p->ID;
        $p->post_content = $this->_buildContent($p);
        if($this->_isPostExisting($id)) {
            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_title = %s WHERE ID = %d", $p->post_title, $id ) );
        } else {
            $wpdb->query( $wpdb->prepare( "INSERT INTO `$wpdb->posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_name`,
                                                                         `post_modified`, `post_modified_gmt`, `guid`, `comment_count`)
                                           VALUES (%s, %s, %s, %s, %s,%s,%s, %s, %s, %s, %s)",
                $id, $p->post_author, $p->post_date, $p->post_date_gmt, $p->post_content, $p->post_title,$p->post_name,
                $p->post_modified, $p->post_modified_gmt, $p->guid, $p->comment_count));
        }
    }

    protected function _buildContent($p) {
        $content = '';
        if(!empty($p->content)) {
            $ct = $p->content;
            if($p->post_type == 'link') {
                $content .= '<figure>';
                $content .= '<a target="_blank" href="'.$ct->link.'"><img src="'.$ct->picture.'" />';
                $content .= $ct->name;
                $content .= '<figcaption>'.$ct->caption.'</figcaption>';
                $content .= '</a>';
                $content .= '</figure>';
            } elseif ($p->post_type == 'video') {
                $content .= '<a target="_blank" href="'.$ct->link.'">';
                if(!empty($ct->picture)) {
                    $content .= '<img src="' . $ct->picture . '" />';
                } else {
                    $content .= "Please Click Here to view the video";
                }
                if(!empty($ct->caption)) {
                    $content .= $ct->caption;
                } else {
                    $content .= "<br />".$this->_getDisplayName($p->post_author);
                }
                $content .= '</a>';
            } else {
                $content .= $p->description;
            }
        }
        return $content;
    }

    protected function _getDisplayName($user_id) {
        global $wpdb;
        return $wpdb->get_var(  $wpdb->prepare( "SELECT display_name FROM {$wpdb->users} WHERE ID = %s", $user_id ) );
    }

    protected function _isPostExisting($postId) {
        global $wpdb;
        if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID = %s", $postId ) ) ) {
            return true;
        } else {
            return false;
        }
    }

    protected function _saveComment($c) {
        global $wpdb;
        $id = $c->comment_ID;
        if($this->_isCommentExisting($id)) {
            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->comments} SET comment_content = %s WHERE comment_ID = %d", $c->comment_content, $id ) );
        } else {
            $wpdb->query(  $wpdb->prepare( "INSERT INTO `$wpdb->comments` (`comment_ID`, `comment_post_ID`, `comment_author`, `comment_date`, `comment_date_gmt`, `comment_content`, `user_id`)
                                           VALUES (%s, %s, %s, %s, %s,%s,%s)",
                $id, $c->comment_post_ID, $c->comment_author, $c->comment_date, $c->comment_date_gmt, $c->comment_content, $c->user_id) );
        }
    }

    protected function _isCommentExisting($id) {
        global $wpdb;
        if ( $wpdb->get_var(  $wpdb->prepare( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_ID = %s", $id ) ) ) {
            return true;
        } else {
            return false;
        }
    }

}