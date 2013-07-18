<?php
class block_teshutu extends block_base {
  /**  初期設定  **/
  function init() {
    $this->title = '提出チェック';		//タイトル
    $this->version = 20081024;	//バージョン
  }
  function has_config() {
  return false;
  }
  function specialization() {
    // load userdefined title and make sure it's never empty
    if (empty($this->config->title)) {
      $this->title = "提出チェック";
    } else {
      $this->title = $this->config->title;
    }
  }

  /**　メインの部分  **/
  function get_content() {
    global $USER, $CFG, $COURSE;
    if (empty($this->config->numberoftags)) {
      $this->config->numberoftags = 24;
    }
    /*sql文作成*/
    //$select = "SELECT * ";
    $select = "SELECT id, time, userid, course, action, url, info ";
    $from = "FROM mdl_log ";

    $l = $row-$l;
    $action1 = "upload ";
    $action2 = "update grades ";
    $time1 = time()-(60*60*$this->config->numberoftags1);
    $time2 = time()-(60*60*$this->config->numberoftags2);//$time1~$time2までの期間

         //$time = time()-(2*60*60*24);//48時間前のtimestamp
         //$time = time()-(7*60*60*24);//一週間前のtimestamp
         //$time=0;
         //$where =  "WHERE action = '".$action."' AND time > ".$time." ";
    $where = "WHERE time < ".$time1." AND time > ".$time2." AND (action = '".$action1."' OR action = '".$action2."') " ;
         //$order = "ORDER BY time DESC ";
    $order = "ORDER BY time DESC ";
    $sql1 = "SELECT * " . $from . $where . $groupby . $order;

    //echo "SQL:".$sql1.";<br>".count_records_sql($sql1)."<br><br>";//sql文確認用
    /*sql文作成ここまで*/

    $check='/^http:\/\/.*\/mod\/assignment\/view\.php.*/';
    if(count_records_sql($sql1) <=0){
      $this->content->text .= "指定の期間でアップロードされたデータはありません。<br>";
    }
    else if ($users = get_records_sql($sql1, 0, 100)){
                  //echo "count".count($users)."<br>";
      foreach ($users as $user) {
	if(preg_match($check, $user->url) ){
	  //課題がアップロードされた場合に表示する。
	  $sql_name = "SELECT * FROM mdl_user WHERE id = ".$user->userid;
        	  //echo "SQL:".$sql_name.";<br><br>";//sql文確認用
	  $sql_role_id = "SELECT * FROM mdl_role_assignments"
	    ." WHERE contextid = 1 AND userid = ".$USER->id
	    ." ORDER BY timemodified DESC ";
	          //echo "sql_role_id:".$sql_role_id.";<br>";//sql文確認用

	  if(get_record_sql($sql_role_id)->roleid == 1){
	    //先生の場合のリンク制御
	    $this->content->text .= date('Y/n/j(D)H:i',$user->time)."<br>"
	      .get_record_sql($sql_name)->username."さんが課題を";
	    $this->content->text .= "<a href=\"".str_replace("view","submissions",$user->url)
	      ."\">アップロードしています。</a><br>";  //リンクurl
	  }
	  else if($user->userid == $USER->id){
	    //学生の場合のリンク制御
	    $this->content->text .= date('Y/n/j(D)H:i',$user->time)."<br>"
	      .get_record_sql($sql_name)->username."さんは課題を<a href=\"";
	    $this->content->text .= $user->url;
	    $this->content->text .= "\">アップロードしています</a>。<br>";
	  }
	  else{
	    continue;
	  }

	  //課題の先生確認の有無チェック
	  $sql_check_saiten = "SELECT info ".$from." WHERE userid = ".$user->userid." AND time = ".$user->time
	    ." AND module = 'assignment' AND action = 'upload' ORDER BY id DESC ";
	               //echo "sql_check_saiten:".$sql_check_saiten.";<br>";//sql文確認用
	  $sql_check_saiten = "SELECT timemarked FROM mdl_assignment_submissions WHERE userid = ".$user->userid
	    ." AND assignment = ".get_record_sql($sql_check_saiten)->info." ";
	               //echo "sql:".$sql_check_saiten.";<br>";//sql文確認用
	  if(count_records_sql($sql_check_saiten) > 0){	    
	    $this->content->text .= "確認済みです。（<a href=\"".$user->url."\">採点を見る</a>）<br><br>";
	  }
	  else{
	    if($user->userid == $USER->id){
	      $this->content->text .= "未確認です。もう少しお待ちください。<br><br>";
	    }else{
	      $this->content->text .= "未確認です。<br><br>";
	    }
	  }
	  //課題の確認の有無チェックend
	}
      }
    }else {
      $this->content->text .= "データの取得に失敗。<br>";
    } 
    //else{$this->content->text .= count($users)."失敗<br>";}
   
    //$this->content->text .= "^^^^^<br>";
    /*ここまで*/
    return $this->content;
    }
  function instance_allow_config() {//config_instance.htmlを使う場合に使用する
    return true;
  }
  function instance_allow_multiple() {//config_instance.htmlを使う場合に使用する
    return true;
  }
  function instance_config_print() {
    global $CFG,$THEME;

    /// set up the numberoftags select field
    $numberoftags1 = array();
    $numberoftags2 = array();
    for($i=0;$i<240;$i++){
      $numberoftags1[$i] = $i;
      $numberoftags2[$i] = $i+1;
    }

    if (is_file($CFG->dirroot .'/blocks/'. $this->name() .'/config_instance.html')) {
      print_simple_box_start('center', '', '', 5, 'blockconfigglobal');
      include($CFG->dirroot .'/blocks/'. $this->name() .'/config_instance.html');
      print_simple_box_end();
    } else {
      notice(get_string('blockconfigbad'), str_replace('blockaction=', 'dummy=', qualified_me()));
    }

  }
}
?>