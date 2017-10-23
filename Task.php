<?php
/**
 * Created by PhpStorm.
 * User: Steve.ma
 * Date: 17/5/4
 * Time: 下午1:27
 */
class TaskController extends ApiControllerModel {

    /**
     * 保存任务
     * @return array|string
     */
    public function savetaskAction(){
        $id = $this->getPost("id", 0);
        $program_id = $this->getPost("program_id", 0);
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "TaskAdd");
        $group_id = $this->checkToken($token);
        $title = $this->getPost("title", '');
        $content = $this->getPost("content", '');
        $claim_end_time = $this->getPost("claim_end_time", '');
        $start_time = $this->getPost("start_time", getDateTimeByTime());
        if($id){
            $res = ModuleTaskModel::saveTask(array(
                "title"         => $title,
                "content"       => $content,
                "claim_end_time" => $claim_end_time,
            ), array(
                "program_id"    => $program_id,
                "group_id"      => $group_id,
                "id"            => $id
            ));
        } else {
            $res = ModuleTaskModel::newTask(array(
                "start_time"    => $start_time,
                "program_id"    => $program_id,
                "group_id"      => $group_id,
                "title"         => $title,
                "content"       => $content,
                "claim_end_time" => $claim_end_time,
                "created_at"    => getDateTimeByTime()
            ));
        }
        if($res){
            return Tools::returnMsg(true, $res, $this->returnType);
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10010,
                "message"   => "操作数据库失败，请联系管理员"
            ));
        }
    }

    /**
     * 任务详情
     * @return array|string
     */
    public function taskinfoAction(){
        $id = $this->getPost("id");
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "Task");
        $res = ModuleTaskModel::TaskInfo("`id`,`program_id`,`group_id`,`title`,`content`,`claim_end_time`,`status`,`end_time`", array(
            "id"    => $id
        ));
        return Tools::returnMsg(true, $res, $this->returnType);
    }

    /**
     * 保存子任务
     * @return array|string
     */
    public function savetaskitemAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "TaskAdd");
        $group_id = $this->checkToken($token);
        $task_id = $this->getPost("task_id", 0);
        $program_id = $this->getPost("program_id", 0);
        $type = $this->getPost("type", '');
        $task_type = $this->getPost("task_type", '');
        $desc = $this->getPost("desc", '');
        $claim_end_time = $this->getPost("claim_end_time", '');
        $user_id = $this->getPost("user_id", 0);
        $user_info = ModuleUserModel::findUser("`id`,`nick`,`phone`,`avatar`,`quote`", array(
            "id" => $user_id
        ));

        $content = '';
        if($type == 'write'){
            $task_item = ModuleTaskModel::TaskItemInfo('`content`', array(
                "task_id"   => $task_id,
                "status"    => 0,
                "type"      => "composing"
            ));
            if(empty($task_item) === FALSE){
                $content = $task_item['content'];
            }
        } else if($type == 'composing'){

            $task_item = ModuleTaskModel::TaskItemInfo('`content`', array(
                "task_id"   => $task_id,
                "status"    => 0,
                "type"      => "write"
            ));
            if(empty($task_item) === FALSE){
                $content = $task_item['content'];
            }
        }


        $item = ModuleTaskModel::TaskItemInfo('id', array(
            "task_id"   => $task_id,
            "program_id"    => $program_id,
            "type"      => $type,
            "status"    => 0
        ));

        $task_type_info = $task_type;
        if($type == 'composing'){
            $task_type = json_encode($task_type, JSON_UNESCAPED_UNICODE);
            $task_type_info = '排版';
        }
        $price = 0;
        foreach($user_info['quote'] as &$v){
            if($v['name'] == $task_type_info){
                $price = $v['value'];
            }
        }


        if(empty($item) === FALSE){
            $res = ModuleTaskModel::saveTaskItem(array(
                "task_type" => $task_type,
                "desc"      => $desc,
                "claim_end_time"    => $claim_end_time,
                "user_id"   => $user_id,
                "price"     => $price,
                "content"   => $content,
                "updated_at"    => time()
            ), array(
                "id"        => $item['id'],
                "group_id"  => $group_id,
                "task_id"   => $task_id,
                "program_id"    => $program_id,
                "type"      => $type
            ));
        } else {
            $res = ModuleTaskModel::newTaskItem(array(
                "task_type" => $task_type,
                "desc"      => $desc,
                "claim_end_time"    => $claim_end_time,
                "user_id"   => $user_id,
                "price"     => $price,
                "group_id"  => $group_id,
                "task_id"   => $task_id,
                "program_id"    => $program_id,
                "content"   => $content,
                "type"      => $type,
                "user_status"   => 0,
                "created_at"    => getDateTimeByTime(),
                "updated_at"    => time()
            ));
        }

        if($res){
            return Tools::returnMsg(true, $res, $this->returnType);
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10010,
                "message"   => "操作数据库失败，请联系管理员"
            ));
        }
    }

    /**
     * 指派给新组员
     * @return array|string
     */
    public function changeuserAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "TaskAdd");
        $group_id = $this->checkToken($token);
        $task_id = $this->getPost("task_id", 0);
        $program_id = $this->getPost("program_id", 0);
        $type = $this->getPost("type", '');
        $user_id = $this->getPost("user_id", 0);
        $item = ModuleTaskModel::TaskItemInfo('id', array(
            "task_id"   => $task_id,
            "program_id"    => $program_id,
            "type"      => $type,
            "status"    => 0
        ));
        if(empty($item) === FALSE){

            $user_info = ModuleUserModel::findUser("`id`,`nick`,`phone`,`avatar`,`quote`", array(
                "id" => $user_id
            ));
            $task_type_info = $item['task_type'];
            if($type == 'composing'){
                $task_type_info = '排版';
            }
            $price = 0;
            foreach($user_info['quote'] as &$v){
                if($v['name'] == $task_type_info){
                    $price = $v['value'];
                }
            }

            $res = ModuleTaskModel::saveTaskItem(array(
                "user_id"       => $user_id,
                "price"         => $price,
                "user_status"   => 0,
                "updated_at"    => time(),
                "send_time"     => getDateTimeByTime()
            ), array(
                "task_id"       => $task_id,
                "program_id"    => $program_id,
//                "group_id"      => $group_id,
                "type"          => $type,
                "id"            => $item['id']
            ));
            if($res){
                return Tools::returnMsg(true, $res, $this->returnType);
            } else {
                return Tools::returnMsg(false, array(
                    "code"  => 10010,
                    "message"   => "操作数据库失败，请联系管理员"
                ));
            }
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10057,
                "message"   => "找不到该任务"
            ));
        }
    }

    /**
     * 查看任务列表
     * @return array|string
     */
    public function listAction(){
//          define('DEBUG_MODE', true);
//          ini_set('display_errors', 'On');
        $token          = $this->getPost("token",           '');
        $this->CheckNode($token, "Task");
        $page = $this->getPost("page", 1);
        $size = $this->getPost("size", 20);
        $type = $this->getPost("type", 'group');
        $search = $this->getPost("search", '');
        $status = $this->getPost("status", 'all');
        $user_id = $this->checkToken($token);
        $user_info = ModuleUserModel::findUser("id,team_id",array(
            "id"    => $user_id
        ));
        $data = array(
            "status"    => array(
                "opera" => "IN",
                "value" => "0,2"
            )
        );

        switch ($status){
            case 'all':
                break;
            case 'nosend':
                $data['user_id'] = 0;
                break;
            case 'wait':
                $data['user_status'] = 0;
                $data['user_id'] = array(
                    "opera" => "NEQ",
                    "value" => 0
                );
                break;
            case 'working':
                $data['task_status'] = 0;
                $data['user_status'] = 1;
                break;
            case 'disappend':
                $data['task_status'] = 0;
                $data['user_status'] = 2;
                break;
            case 'commit':
                $data['task_status'] = 1;
                break;
            case 'reback':
                $data['task_status'] = 3;
                break;
            case 'end':
                $data['task_status'] = 2;
                break;
            case 'stop':
                $data['status'] = 2;
                break;
        }

        if($user_info['team_id'] == TEAM_ADMIN_ID) {

        } else if($user_info['team_id'] == TEAM_CHIEF_ID){
            $chief = array();
            $list = ModuleUserModel::ChiefList(array(
                "chief_id"  => $user_id,
                "status"    => 0
            ), false);
            if(empty($list) === FALSE) {
                foreach ($list as &$v) {
                    $chief[] = $v['group_id'];
                }
            }
            $data['group_id'] = array(
                "opera" => "IN",
                "value" => implode(",", $chief)
            );
        } else if($user_info['team_id'] == TEAM_GROUP_ID){
            $data['group_id'] = $user_id;
        } else {
            $data['user_id'] = $user_id;
        }
//
//        if($type == 'user'){
//            $data['user_id'] = $user_id;
//        } else {
//            if($user_id != 1){
//                $data['group_id'] = $user_id;
//            }
//        }

        if(empty($search) === FALSE) {
            $task_data = array(
                "status"    => array(
                    "opera" => "IN",
                    "value" => "0,1,2"
                ),
                "title"     => array(
                    "opera" => "LIKE",
                    "value" => $search
                )
            );
            $taskList = ModuleTaskModel::TaskList("id", $task_data);
            $task_arr = array();
            if(empty($taskList) === FALSE){
                foreach($taskList as &$v){
                    $task_arr[] = $v['id'];
                }
            }
            if(empty($task_arr) === FALSE){
                $data['task_id'] = array(
                    "opera" => "IN",
                    "value" => implode(",", $task_arr)
                );
            }
        }

        $total = ModuleTaskModel::TaskItemInfo("count(1) as total", $data);
        if($total['total'] > 0){
            $offset = ($page - 1) * $size;
            $list = ModuleTaskModel::TaskItemList("`id`,`task_id`,`program_id`,`group_id`,`type`,`claim_end_time`,`user_id`,`user_status`,`user_reason`,`task_type`,`reason`,`task_status`,`status`,`price`,`content`", $data, array($offset, $size));
        } else {
            $list = array();
        }
        if(empty($list) === FALSE){
            foreach ($list as &$res) {
                if($res['user_id']) {
                    $res['user'] = ModuleUserModel::findUser("`id`,`nick`,`phone`,`avatar`,`quote`", array(
                        "id" => $res['user_id']
                    ));
                }
                $res['task'] = ModuleTaskModel::TaskInfo('`title`,`content`,`claim_end_time`', array(
                    "id"   => $res['task_id'],
                    "program_id"    => $res['program_id']
                ));
                $res['type_text'] = getTaskTypeText($res['type']);
                $res['status_text'] = getTaskStatusText($res['task_status'], $res['user_status'], $res['status']);
                $res['program'] = ModuleProgramModel::ProgInfo('wechat', array(
                    "id"    => $res['program_id']
                ));
                if(empty($res['program']) === FALSE){
                    $res['wechat'] = ModuleProgramModel::weConf("`appid`,`name`,`alias`,`head_img`", array(
                        "appid" => $res['program']['wechat'],
                        "program_id"    => $res['program_id']
                    ));
                }

            }
        }
        return Tools::returnMsg(true, array(
            "total"     => $total['total'],
            "list"      => $list
        ), $this->returnType);
    }

    /**
     * 子任务详情
     * @return array|string
     */
    public function iteminfoAction(){
        $token          = $this->getPost("token",           '');
        $this->CheckNode($token, "Task");
        $user_id = $this->checkToken($token);
        $id = $this->getPost("id", 0);
        $res = ModuleTaskModel::TaskItemInfo('`id`,`task_id`,`program_id`,`group_id`,`desc`,`type`,`claim_end_time`,`task_type`,`user_id`,`user_status`,`reason`,`task_status`,`status`,`price`,`content`', array(
            "id" => $id,
            "status"    => array(
                "opera" => "IN",
                "value" => "0,2"
            )
        ));
        //查看登录人权限是否为管理员
        $team_id = ModuleUserModel::findUser('`team_id`',array("id" => $user_id));
        //判断登录人是否为子任务人
        if($res['user_id'] == $user_id){
            $res['u_type'] = 'user';
        }
        //判断登录人是否为子任务人组长 
        else if($res['group_id'] == $user_id){
            $res['u_type'] = 'group';
        }
        //判断登录人是否为管理员
        else if($team_id['team_id'] == 1){
            $res['u_type'] = 'group';
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10058,
                "message"   => "找不到该任务或者没有权限"
            ));
        }
        $res['task'] = ModuleTaskModel::TaskInfo('`title`,`content`,`files`,`claim_end_time`', array(
            "id"   => $res['task_id'],
            "program_id"    => $res['program_id']
        ));
        $res['type_text'] = getTaskTypeText($res['type']);
        $res['status_text'] = getTaskStatusText($res['task_status'], $res['user_status'], $res['status']);
        $res['program'] = ModuleProgramModel::ProgInfo('wechat,title', array(
            "id"    => $res['program_id']
        ));
        if($res['type'] == 'composing'){
            $res['task_type'] = json_decode($res['task_type'], true);
        }
        if(empty($res['content']) === TRUE){
            if($res['type'] == 'write'){
                $task_item = ModuleTaskModel::TaskItemInfo('`content`', array(
                    "task_id"   => $res['task_id'],
                    "status"    => 0,
                    "type"      => "composing"
                ));
                if(empty($task_item) === FALSE){
                    $res['content'] = $task_item['content'];
                }
            } else if($res['type'] == 'composing'){

                $task_item = ModuleTaskModel::TaskItemInfo('`content`', array(
                    "task_id"   => $res['task_id'],
                    "status"    => 0,
                    "type"      => "write"
                ));
                if(empty($task_item) === FALSE){
                    $res['content'] = $task_item['content'];
                }
            }
        }
        if(empty($res['program']) === FALSE){
            $res['wechat'] = ModuleProgramModel::weConf("`appid`,`name`,`alias`,`head_img`", array(
                "appid" => $res['program']['wechat'],
                "program_id"    => $res['program_id']
            ));
        }
        return Tools::returnMsg(true, $res, $this->returnType);
    }

    /**
     * 用户接受任务
     * @return array|string
     */
    public function useragreetaskAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "Task");
        $user_id = $this->checkToken($token);
        $id = $this->getPost("id", 0);

        $task_info = ModuleTaskModel::TaskItemInfo('`id`,`task_id`', array(
            "id" => $id,
            "user_id" => $user_id,
            "user_status" => 0,
            "task_status" => 0
        ));
        if (empty($task_info) === FALSE) {
            $res = ModuleTaskModel::UserAgreeTask($id, $task_info['task_id'], $user_id);
            if ($res) {
                return Tools::returnMsg(true, $res, $this->returnType);
            } else {
                return Tools::returnMsg(false, array(
                    "code" => 10010,
                    "message" => "操作数据库失败，请联系管理员"
                ));
            }
        } else {
            return Tools::returnMsg(false, array(
                "code" => 10058,
                "message" => "不存在的任务或没有权限"
            ));
        }
    }

    /**
     * 用户拒绝任务
     * @return array|string
     */
    public function userdisagreetaskAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "Task");
        $user_id = $this->checkToken($token);
        $id = $this->getPost("id",  0);
        $user_reason = $this->getPost("user_reason", '');
        $task_info = ModuleTaskModel::TaskItemInfo('`id`,`task_id`', array(
            "id"    => $id,
            "user_id"   => $user_id,
            "user_status"   => 0,
            "task_status"   => 0
        ));
        if(empty($task_info) === FALSE) {
            $res = ModuleTaskModel::UserDisAgreeeTask($id, $user_id, $user_reason);
            if($res){
                return Tools::returnMsg(true, $res, $this->returnType);
            } else {
                return Tools::returnMsg(false, array(
                    "code"  => 10010,
                    "message"   => "操作数据库失败，请联系管理员"
                ));
            }
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10058,
                "message"   => "不存在的任务或没有权限"
            ));
        }
    }

    /**
     * 用户提交任务
     * @return array|string
     */
    public function usertaskcommitAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "Task");
        $user_id = $this->checkToken($token);
        $id = $this->getPost("id",  0);
        $task_info = ModuleTaskModel::TaskItemInfo('`id`,`task_id`', array(
            "id"    => $id,
            "user_id"   => $user_id,
            "user_status"   => 1
        ));
        if(empty($task_info) === FALSE) {
            $res = ModuleTaskModel::UserCommitTask($id, $user_id);
            if($res){
                return Tools::returnMsg(true, $res, $this->returnType);
            } else {
                return Tools::returnMsg(false, array(
                    "code"  => 10010,
                    "message"   => "操作数据库失败，请联系管理员"
                ));
            }
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10058,
                "message"   => "不存在的任务或没有权限"
            ));
        }
    }

    /**
     * 组长定稿
     * @return array|string
     */
    public function groupcommitAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "TaskAdd");
        $group_id = $this->checkToken($token);
        $id = $this->getPost("id",  0);
        $task_info = ModuleTaskModel::TaskItemInfo('`id`,`task_id`,`type`, `content`,`task_type`', array(
            "id"        => $id,
            "group_id"  => $group_id
        ));
        if(empty($task_info) === FALSE) {
            $res = ModuleTaskModel::GroupCommitTask($id, $group_id, $task_info['task_id'], $task_info['type'], $task_info['content'], $task_info['task_type']);
            if($res){
                return Tools::returnMsg(true, $res, $this->returnType);
            } else {
                return Tools::returnMsg(false, array(
                    "code"  => 10010,
                    "message"   => "操作数据库失败，请联系管理员"
                ));
            }
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10058,
                "message"   => "不存在的任务或没有权限"
            ));
        }
    }

    /**
     * 组长驳回
     * @return array|string
     */
    public function grouprollbackAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "TaskAdd");
        $group_id = $this->checkToken($token);
        $id = $this->getPost("id",  0);
        $reason = $this->getPost("reason", '');
        $task_info = ModuleTaskModel::TaskItemInfo('`id`,`task_id`,`task_backtimes`', array(
            "id"        => $id,
            "group_id"  => $group_id
        ));
        if ($task_info) {
            $task_backtimes = $task_info['task_backtimes']+1;
        }
        if(empty($task_info) === FALSE) {
            $res = ModuleTaskModel::GroupRollback2Task($id, $group_id, $task_backtimes, $reason);
            if($res){
                return Tools::returnMsg(true, $res, $this->returnType);
            } else {
                return Tools::returnMsg(false, array(
                    "code"  => 10010,
                    "message"   => "操作数据库失败，请联系管理员"
                ));
            }
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10058,
                "message"   => "不存在的任务或没有权限"
            ));
        }
    }

    /**
     * 组员保存工作内容
     * @return array|string
     */
    public function savecontentAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "Task");
        $user_id = $this->checkToken($token);
        $id = $this->getPost("id",  0);
        $content = $this->getPost("content",  '');
        $task_info = ModuleTaskModel::TaskItemInfo('`id`,`task_id`', array(
            "id"    => $id,
            "user_id"   => $user_id,
            "user_status"   => 1
        ));
        if(empty($task_info) === FALSE) {
            $res = ModuleTaskModel::saveTaskItem(array(
                "content"   => $content
            ), array(
                "id"        => $id,
                "user_id"   => $user_id
            ));
            if($res){
                return Tools::returnMsg(true, $res, $this->returnType);
            } else {
                return Tools::returnMsg(false, array(
                    "code"  => 10010,
                    "message"   => "操作数据库失败，请联系管理员"
                ));
            }
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10058,
                "message"   => "不存在的任务或没有权限"
            ));
        }
    }

    /**
     * 组长终止任务
     * @return array|string
     */
    public function stoptaskAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "TaskAdd");
        $group_id = $this->checkToken($token);
        $task_id = $this->getPost("task_id", 0);
        $program_id = $this->getPost("program_id", 0);
        $type = $this->getPost("type", '');
        $item = ModuleTaskModel::TaskItemInfo('id', array(
            "task_id"   => $task_id,
            "program_id"    => $program_id,
            "type"      => $type,
            "status"    => 0
        ));
        if(empty($item) === FALSE){
            $res = ModuleTaskModel::GroupStopTask($item['id'], $group_id);
            if($res){
                return Tools::returnMsg(true, $res, $this->returnType);
            } else {
                return Tools::returnMsg(false, array(
                    "code"  => 10010,
                    "message"   => "操作数据库失败，请联系管理员"
                ));
            }
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10057,
                "message"   => "找不到该任务"
            ));
        }
    }

    /**
     * 组长结束任务
     * @return array|string
     */
    public function endtaskAction(){
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "TaskAdd");
        $group_id = $this->checkToken($token);
        $task_id = $this->getPost("task_id", 0);
        $program_id = $this->getPost("program_id", 0);
        $task = ModuleTaskModel::TaskInfo('`id`,`title`' , array(
            "id"    => $task_id,
            "group_id"  => $group_id,
            "program_id"    => $program_id
        ));
        $opera = array();
        $article_id = empty($task['write']['content']) ? $task['composing']['content'] : $task['write']['content'];
        $qc         = $this->getQcangObj();
        $opera[] = $qc->MaterialDel(array(
            "id"    => $article_id,
            "type"  => "article",
            "user_id"   => $program_id,
            "status"    => '0'
        ));

        $content = $task['design']['content'];
        if(empty($content) === FALSE){
            $content = explode(",", $content);
            $i = 1;
            foreach($content as &$v){
                $opera[] = $qc->MaterialAdd(array(
                    "type"  => "image",
                    "title" => $task['title']."_".$i,
                    "user_id"   => $program_id,
                    "isshare"   => 0,
                    "content"   => $v
                ));
            }
        }
        if(checkArrayResult($opera)){
            $res = ModuleTaskModel::GroupEndTask($task_id, $group_id);
            if($res){
                return Tools::returnMsg(true, $res, $this->returnType);
            } else {
                return Tools::returnMsg(false, array(
                    "code"  => 10010,
                    "message"   => "操作数据库失败，请联系管理员"
                ));
            }
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10057,
                "message"   => "找不到该任务"
            ));
        }
    }
    /**
     * 群发文章
     * @return array|string
     */
    public function sendAction(){
        $program_id = $this->getPost("program_id", 0);
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "TaskAdd");
        $group_id = $this->checkToken($token);
        $ids = $this->getPost("ids", '');
        $where = array(
            "id"    => $program_id,
        );
        if($group_id != 1){
            $where['group_id'] = $group_id;
        }
        $program = ModuleProgramModel::ProgInfo('`wechat`', $where);
        $appid = $program['wechat'];
        if(empty($appid) === TRUE){
            return Tools::returnMsg(false, array(
                "code"  => 10099,
                "message"   => "公众号授权错误"
            ));
        }
        $qc         = $this->getQcangObj();
        $arr = array();
        $ids = explode(",", $ids);
        if(empty($ids) === FALSE) {
            foreach ($ids as &$article_id) {
                $article = $qc->MaterialInfo(array(
                    "id" => $article_id,
                    "type" => "article",
                    "user_id"   => $program_id
                ));
                if (empty($article) === FALSE) {
                    $arr[] = array(
                        "cover" => $article['cover'],
                        "cover_sm" => $article['cover_sm'],
                        "title" => $article['title'],
                        "author" => $article['author'],
                        "digest" => $article['digest'],
                        "show_cover" => $article['show_cover'],
                        "content" => str_replace("\n", "", $article['content']),
                        "source_url" => $article['source_url'],
                        "send_ignore" => $article['send_ignore'],
                    );
                }
            }
        }
        foreach ($arr as &$v){
            if ($v['send_ignore']==1){

            }
        }
        $sendArticle = array(
            "appid"     => $appid,
            "sendto"    => json_encode(array(
                "type"  => "all"
            )),
//            "sendto"    => json_encode(array(
//                "type"  => "list",
//                "openlist" => array(
//                    "oztIXvzfWoLYozLdcWcJyV1GcTBQ",
//                    "oztIXv5Glc7lGhA7Y8SX8OYTwh2g"
//                    "oztIXv3zcuMtdicJoL_9eomhp32w"
//                )
//            )),
            "type"      => 'article',
            "content"   => json_encode($arr,JSON_UNESCAPED_UNICODE),
            "sendtime"  => getDateTimeByTime(time()+100)
            //原创校验
            // 'send_ignore'=>
        );
        $res = $qc->WechatMassSend($sendArticle);
        if($res){
            return Tools::returnMsg(true, $res, $this->returnType);
        } else {
            return Tools::returnMsg(false, array(
                "code"  => 10010,
                "message"   => "操作数据库失败，请联系管理员"
            ));
        }
        return false;
    }
    /**
     * @param KiKi.Bai
     * time:2017-9-6 15:08:35
     * type：获取原创状态
    */
    public function DeclareOriginalAction(){
        $program_id = $this->getPost("program_id", 0);
        $article_id = $this->getPost("content", 0);
        $token = $this->getPost("token", '');
        $group_id = $this->checkToken($token);
        $qc         = $this->getQcangObj();
        $article = $qc->MaterialInfo(array(
                    "id" => $article_id,
                    "type" => "article",
                    "user_id"   => $program_id
                ));
        if($article){
            return Tools::returnMsg(true, array(
                "send_ignore"  => $article['send_ignore'],
                "need_open_comment"   => $article['need_open_comment'],
                "only_fans_can_comment"   => $article['only_fans_can_comment'],

            ), $this->returnType);
        }else{
            $res = 0;
            return Tools::returnMsg(true, array(
                "send_ignore"  => 0,
            ), $this->returnType);
        }
        return false;
    }
    /**
     * @param KiKi.Bai
     * time:2017-9-6 15:08:35
     * type：修改文章状态
    */
    public function DeclareOriginalEditAction(){
        $program_id = $this->getPost("program_id", 0);
        $task_id = $this->getPost("task_id", 0);
        $token = $this->getPost("token", '');
        $group_id = $this->checkToken($token);
        $where = array(
            "task_id"    => $task_id,
        );
        $article= ModuleTaskModel::TaskItemInfo('`content`', $where);
        $article_id = $article['content'];
        $qc         = $this->getQcangObj();
        $article = $qc->MaterialInfo(array(
                    "id" => $article_id,
                    "type" => "article",
                    "user_id"   => $program_id
                ));
        if($article){
            $res = $article['send_ignore'];
            return Tools::returnMsg(true, $res, $this->returnType);
        }else{
            $res = 0;
            return Tools::returnMsg(true, $res, $this->returnType);
        }
        return false;
    }
    /*
     * @param KiKi.Bai
     * time:2017年8月3日11:32
     * type：排版默认
     * */
    public function task_typeAction(){
        $id =$this->getPost("program_id",0);
        $type =$this->getPost("type");
        $type = "composing";
        $token = $this->getPost("token", '');
        $this->CheckNode($token, "Task");;
        $sql = "select task_type from task_item where program_id=$id AND type='$type' ORDER BY id  desc limit 1";
        $res = Db::getInstance(false)->getRow($sql);
        $res = json_decode($res['task_type']);
        return Tools::returnMsg(true, $res, $this->returnType);
    }
}