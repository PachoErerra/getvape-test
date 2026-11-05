<?php
if (defined("IS_DEBUG") && IS_DEBUG) {
    global $debugModelQueries;
    global $debugControllerActions;

    //SORT LOCAL TIMES
    $debugModelQueriesSortDescByTime = array();
    foreach ($debugModelQueries as $arr) {
        $key = (string)substr(str_replace(['.', '-', 'E'], '', (string)$arr['time']), 0, 13);
        $debugModelQueriesSortDescByTime[$key] = $arr;
    }
    krsort($debugModelQueriesSortDescByTime);

    //SORT LOCAL TIMES
    $debugControllerActionsSortDescByTime = array();
    foreach ($debugControllerActions as $arr) {
        $key = (string)substr(str_replace(['.', '-', 'E'], '', (string)$arr['time']), 0, 13);
        $debugControllerActionsSortDescByTime[$key] = $arr;
    }
    krsort($debugControllerActionsSortDescByTime);

    ?>

    <style>
        #debug-wrapper {
            position: fixed;
            width: 100%;
            bottom: 0;
            left: 0;
            background: #fff;
            z-index: 1000;
        }

        #debug-panel-header {
            display: block;
            width: 100%;
            height: 20px;
            border: 1px solid #ddd;
            border-bottom: none;
            background: #f9ffa3;
        }

        #debug-panel-body {
            position: relative;
            display: none;
            width: 100%;
            bottom: 0;
            left: 0;
            background: #fff;
            height: 400px;
            overflow-y: auto;
        }

        #debug-toggle {
            position: absolute;
            font-size: 20px;
            cursor: pointer;
            top: -2px;
            right: 10px;
        }

        #debug-panel-body .nav-tabs > li > a {
            border-radius: 0;
        }

        #debug-panel-body table {
            border: 1px solid #ddd;
        }

        #debug-panel-body table thead {
            font-weight: bold;
        }
    </style>

    <div id="debug-wrapper">
        <div id="debug-panel-header">
            <b>DEBUG PANEL</b> <span style="color:red">(by digital elephant)</span>
            <div id="debug-toggle"><i class="fa fa-angle-right" aria-hidden="true"></i></div>
        </div>
        <div id="debug-panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#debug-panel-tab-action">Actions</a></li>
                <li><a data-toggle="tab" href="#debug-panel-tab-query">Queries</a></li>
            </ul>

            <div class="tab-content">
                <div id="debug-panel-tab-action" class="tab-pane fade in active">
                    <h3>Actions</h3>
                    <p><kbd>Count: <?= count($debugControllerActionsSortDescByTime) ?></kbd></p>
                    <div class="row">
                        <div class="col-sm-6">
                            <table class="table table-hover table-bordered">
                                <thead>
                                <tr>
                                    <td>Class->method</td>
                                    <td>Time (mcsec)</td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($debugControllerActionsSortDescByTime)) { ?>
                                    <?php foreach ($debugControllerActionsSortDescByTime as $debugControllerAction) { ?>
                                        <tr>
                                            <td><?= $debugControllerAction['class'] . ' -> ' . $debugControllerAction['method'] ?></td>
                                            <td><?= $debugControllerAction['time'] ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="debug-panel-tab-query" class="tab-pane fade">
                    <h3>Queries</h3>
                    <p><kbd>Count: <?= count($debugModelQueriesSortDescByTime) ?></kbd></p>
                    <table class="table table-hover table-bordered">
                        <thead>
                        <tr>
                            <td width="20%">File / Class->method</td>
                            <td width="10%">Time (mcsec)</td>
                            <td width="">Query</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($debugModelQueriesSortDescByTime as $debugModelQuery) { ?>
                            <tr>
                                <td>
                                    <?= $debugModelQuery['file']; ?><br>
                                    <b><?= $debugModelQuery['class:method']; ?></b>
                                </td>
                                <td><?= $debugModelQuery['time']; ?></td>
                                <td><?= $debugModelQuery['query']; ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        $('#debug-panel-header').click(function () {
            $('#debug-panel-body').slideToggle('slow');
        });
    </script>
<?php } ?>
