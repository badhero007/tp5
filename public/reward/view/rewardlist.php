
<?php
    require_once '../core/Rds.php';
    require_once '../core/common.php';
    require_once '../static/template/top.phtml';
    require_once '../static/template/left.phtml';

    $redis = Rds::getInstance();
    $rewards = unserialize($redis->get('rewards'));
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Home
            <small>奖励列表</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">奖励列表</li>
        </ol>
    </section>
    <div class="box-body">

        <table  id="table1" class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>id</th>
                <th>总数</th>
                <th>持续时间</th>
                <th>每天最多领取次数</th>
                <th>领取方式</th>
                <th>生效时间</th>
                <th>失效时间</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody id="table_body">
            <?php if($rewards):?>
                <?php foreach ($rewards as $key => $reward): ?>
                    <tr>
                        <td><?php echo $reward['id'] ?></td>
                        <td><?php echo $reward['sum'] ?></td>
                        <td><?php echo $reward['duration'] ?></td>
                        <td><?php echo $reward['times'] ?></td>
                        <td><?php
                            echo $reward['type'] == 'average' ? '平均领取' : '递减领取'
                            ?>
                        </td>
                        <td><?php echo $reward['date_start'] ?></td>
                        <td><?php echo $reward['date_end'] ?></td>
                        <td>
<!--                            <a href="--><?php //echo url('/reward/reward/edit',['rid'=>$reward['id']],false) ?><!--"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a>-->
<!--                            <a onclick="if(confirm('确定删除?')==false)return false;" href="--><?php //echo url('/reward/reward/delete',['rid'=>$reward['id']],false) ?><!--"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>-->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
            <tfoot>

            </tfoot>
        </table>

    </div>
</div>

<?php require_once '../static/template/footer.phtml'?>

