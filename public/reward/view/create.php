<?php 
require_once '../static/template/top.phtml';
require_once '../static/template/left.phtml';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Home
            <small>领取报表</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">创建奖励</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-default">
            <div class="box-body">

                <form action="../control/createReward.php" method="post">

                    <div class="form-group">
                        <label for="sum">总数</label>
                        <input class="form-control" id="sum" name="sum" placeholder="总数">
                    </div>

                    <div class="form-group">
                        <label for="type">获取方式</label>
                        <div class="radio">
                            <label><input type="radio" name="type" value="desc">递减获取</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="type" value="average">平均获取</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="duration">持续时间(多少天领取完毕)</label>
                        <input class="form-control" id="duration" name="duration" placeholder="持续时间(多少天领取完毕)">
                    </div>

                    <div class="form-group">
                        <label for="times">每天可领取几次</label>
                        <input class="form-control" id="times" name="times" placeholder="每天可领取几次">
                    </div>

                    <div class="row">
                        <div class='col-sm-6'>
                            <div class="form-group">
                                <label>生效时间：</label>
                                <!--指定 date标记-->
                                <div class='input-group date' id='start'>
                                    <input type='text' class="form-control" name="date_start" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                </div>
                            </div>
                        </div>
                        <div class='col-sm-6'>
                            <div class="form-group">
                                <label>失效时间：</label>
                                <!--指定 date标记-->
                                <div class='input-group date' id='end'>
                                    <input type='text' class="form-control" name="date_end" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-info">提交</button>
                </form>
            </div>
        </div>
    </section>
</div>

<?php require_once '../static/template/footer.phtml'?>

<script>
    $(function () {
        $('#start').datetimepicker({
            format:'yyyy/mm/dd HH:ii:ss',
            language:'zh-CN',
        })

        $('#end').datetimepicker({
            format:'yyyy/mm/dd HH:ii:ss',
            language:'zh-CN',
        })

        $('form').bootstrapValidator({

            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                sum: {
                    validators: {
                        notEmpty: {
                            message: '总数不能为空'
                        },
                        numeric:  {
                            message:  '必须为数字'
                        }
                    }
                },
                type: {
                    validators: {
                        notEmpty: {
                            message: '获取方式不能为空'
                        }
                    }
                },
                duration: {
                    validators: {
                        notEmpty: {
                            message: '持续时间不能为空'
                        },
                        numeric:  {
                            message:  '必须为数字'
                        }
                    }
                },
                times: {
                    validators: {
                        notEmpty: {
                            message: '每天领取次数不能为空'
                        },
                        numeric:  {
                            message:  '必须为数字'
                        }
                    }
                },
                date_start: {
                    validators: {
                        notEmpty: {
                            message: '生效时间不能为空'
                        }
                    }
                },
                date_end: {
                    validators: {
                        notEmpty: {
                            message: '失效时间不能为空'
                        }
                    }
                }
            }
        });
    })
</script>