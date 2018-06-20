<?php
/**
 * Created by PhpStorm.
 * User: lego
 * Date: 2017/12/21
 * Time: 14:28
 */
    require_once '../core/Rds.php';
    require_once '../core/common.php';
    require_once '../static/template/top.phtml';
    require_once '../static/template/left.phtml';

    $nowpage = isset($_GET['page']) ? $_GET['page'] : 1;
    $rid = isset($_POST['rid']) ? $_POST['rid'] : '';
    $uid = isset($_POST['uid']) ? $_POST['uid'] : '';
    $export = isset($_POST['export']) ? $_POST['export'] : '';
    $redis = Rds::getInstance();
    $data = unserialize($redis->get('allrecord'));

    if($rid){
        foreach ($data as $key => $val) {
            if ($data[$key]['rid'] != $rid) unset($data[$key]);
        }
    }
    if($uid){
        foreach ($data as $key => $val) {
            if ($data[$key]['user'] != $uid) unset($data[$key]);
        }
    }
    if($export){
        $finalData = [
            'title' => '领取明细',
            'rows' => $data,
            'map' => [
                ['key' => '领取时间','value' => 'time'],
                ['key' => '用户','value' => 'user'],
                ['key' => '领取积分','value' => 'points'],
                ['key' => '奖励id','value' => 'rid']
            ]
        ];
        exportExcel($finalData);
    }

    $limits = 20;
    $pages = ceil(count($data)/$limits);
    if(!$data){
        $records = [];
    } else {
        $records = array_slice($data,($nowpage-1)*$limits,$limits);
    }
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Home
            <small>领取报表</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">领取报表</li>
        </ol>
    </section>
    <div class="box-body">
        <form class="form-inline" method="post" >
            <div class="form-group">
                <label for="exampleInputName2">奖励ID</label>
                <input type="text" name="rid" class="form-control" id="rid" placeholder="奖励ID" value="<?php echo $rid ?>">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail2">用户ID</label>
                <input type="text" name="uid" class="form-control" id="uid" placeholder="用户ID" value="<?php echo $uid ?>">
            </div>
            <button type="submit" class="btn btn-info">搜索</button>
            <button type="submit" name="export" value="export" class="btn btn-danger">导出</button>
        </form>
    </div>
    <div class="box-body">
        <table  id="table1" class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>日期</th>
                <th>用户</th>
                <th>领取积分</th>
                <th>奖励id</th>
            </tr>
            </thead>
            <tbody id="table_body">
            <?php foreach ($records as $key => $record): ?>
                <tr>
                    <td><?php echo $record['time'] ?></td>
                    <td><?php echo $record['user'] ?></td>
                    <td><?php echo $record['points'] ?></td>
                    <td><?php echo $record['rid'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>

            </tfoot>
        </table>
        <div id="result"></div>

        <!-- /.box-body -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li>
                    <?php $page = ($nowpage-1) > 0 ? $nowpage - 1 : 1;?>
                    <a href="<?php echo '/reward/view/recordlist.php?page='.$page; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <li>
                        <a href="<?php echo '/reward/view/recordlist.php?page='.$i ?>"><?php echo $i ?></a>
                    </li>
                <?php endfor; ?>
                <li>
                    <?php $page = ($nowpage+1) < $pages ? $nowpage + 1 : $pages;?>
                    <a href="<?php echo '/reward/view/recordlist.php?page='.$page; ?>" aria-label="next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<?php require_once '../static/template/footer.phtml'?>
