<?php
define('fullDirPath', dirname(__FILE__));
define('HAS_LOADED', true);
include_once (fullDirPath . "/../common/base.php");
include_once (fullDirPath . "/class.sql.php");
$newSQL = new newSQL();

$navAction = 'boxer';
if(!empty($_POST['action'])):

    $action = $_POST['action'];
    if($action == 'addSubscription'):
        $addedSubscription = $newSQL->add_subscription($_POST['boxer_id'],$_POST['group_id'], $_POST['paymentType_id'], $_POST['subscriptionType_id'], date("Y-m-d", strtotime($_POST['begin_date'])), date("Y-m-d", strtotime($_POST['end_date'])));
        echo $addedSubscription;
    elseif($action == 'addComment'):
        $commentAdded = $newSQL->add_comment_to_boxer($_POST['boxer_id'], utf8_decode($_POST['comment']), date('Y-m-d'), $_SERVER['REMOTE_USER']);
        echo $commentAdded;
    elseif($action == 'addContact'):
        $contactAdded = $newSQL->add_a_contact_to_boxer($_POST['boxer_id'], utf8_decode($_POST['name']), $_POST['phone'], utf8_decode($_POST['email']));
        echo $contactAdded;
    elseif($action == 'updateBoxer'):
        $updateBoxer = $newSQL->update_boxer($_POST['boxer_id'],  utf8_decode($_POST['name']), $_POST['kt'], $_POST['phone'], utf8_decode($_POST['email']), $_POST['rfid']);
        echo $updateBoxer;
    elseif($action == 'updateImage'):
        $updateImage = $newSQL->update_image($_POST['boxer_id'], $_POST['path']);
        echo $updateImage;
    endif;

elseif(!empty($_GET['boxerID'])):
    $id = $_GET['boxerID'];

    $infoSideBar = $newSQL->list_structured_boxer_info($id, $name);
    $userImage = $newSQL->get_image_path_for_user($id);
    $contactInfo = $newSQL->get_structured_contact_info($id);
    $subscriptions = $newSQL->get_table_of_subscriptions($id);
    $comments = $newSQL->get_structured_comments($id);
    $CheckIns = $newSQL->list_structured_attendance_for_user($id);
    $checkinCountsForMonth = $newSQL->get_attendance_count_for_user_in_month($id);
    $percentageOfMonth = ($checkinCountsForMonth[0] / date('t')) * 100;

    require_once (fullDirPath . "/../config.php");
    $config = ConfigClass::getConfig();

    $pageTitle = "Upplýsingar & Greiðsluyfirlit";
    include_once (fullDirPath . "/head.php");
    include_once (fullDirPath . "/nav-def.php");
    ?>

    <div class="container">
        <div class="col-md-3">
            <br />
          <!-- Boxer image -->
            <div class="slim" data-service="async.php" data-ratio="1:1" data-size="300,300" data-did-upload="imageUpload">
                <input type="file" name="slim[]"/>
                <?php if(!empty($userImage['image'])){
                    echo '<img src="'. sprintf('%s/%s', $config['USER_IMAGE_PATH'], $userImage['image'] ) . '" alt="Profile Picture">';
                }
                ?>
            </div>
            <?php echo 'Mæting:'; ?>
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $percentageOfMonth,'%';?>">
                    <?php echo $checkinCountsForMonth[0],'/', date('t'); ?>
                </div>
            </div>
           <!-- Boxer info -->
          <?php
            if(!$infoSideBar){
              print '<h3 class="text-danger">Engar Upplýsingar fundust um þennan notanda</h3>';
            } else {
              print utf8_encode($infoSideBar);
            }

            echo $contactInfo;

            echo '<div id="comments">
                    <h3> &nbsp; Athugasemdir</h3>';
            echo $comments;
            echo '</div>';
            ?>
            <form class="form-horizontal" id="addComment" method="POST" action="">
                <fieldset>
                    <input type="hidden" name="action" value="addComment" />
                    <input type="hidden" class="form-control" id="boxer_id" name="boxer_id" value="<?php echo $id;?>" />
                    <div class="form-group">
                        <div class="col-lg-12">
                            <textarea class="form-control" rows="3" id="comment" name="comment" placeholder="Byrjaðu að skrifa"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-12">
                            <button type="submit" name="addComment" class="btn btn-primary">Skrá athugasemd</button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
        <!-- Greiðslu upplýsingar -->
        <div class="col-md-9">
            <h3><center> Greiðsluyfirlit</center></h3>
            <table id="subscription_info" class="table table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th>Hópur</th>
                        <th>Greiðsluaðferð</th>
                        <th>Tegund skráningar</th>
                        <th>Keypt þann</th>
                        <th>Rennur út</th>
                    </tr>
                </thead>
                <tbody>
                      <?php
                        if(!$subscriptions){
                          print '<p class="text-danger">Engar Greiðslur fundust á þennan iðkanda</p>';
                        } else {
                          print UTF8_encode($subscriptions);
                        }?>
                </tbody>
            </table>
        </div>
    </div>


  <!--  Add Subscription modal -->
  <div class="modal fade" id="addSubscriptionModal" tabindex="-1" role="dialog" aria-labelledby="addSubscriptionLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="addSubscriptionLabel"><strong><i class="fa fa-ticket fa-lg" aria-hidden="true"></i> Kaupa áskrift</strong></h4>
          </div>
          <div class="modal-body">
              <form class="form-horizontal" id="addSubscription" method="POST" action="">
                  <fieldset>
                      <input type="hidden" name="action" value="addSubscription" />
                      <div class="form-group">
                          <label for="inputID" class="col-lg-2 control-label">ID</label>
                          <div class="col-lg-10">
                              <input type="text" class="form-control" id="boxer_id" name="boxer_id" value="<?php echo $id;?>" readonly />
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="boxerName" class="col-lg-2 control-label">Iðkandi</label>
                          <div class="col-lg-10">
                              <input type="text" class="form-control" id="boxerName" name="boxer_name" value="<?php print $name; ?>" disabled>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="group_id" class="col-lg-2 control-label">Hópur</label>
                          <div class="col-lg-10">
                              <select class="form-control" id="group_id" name="group_id" required>
                                  <?php print UTF8_encode($newSQL->combo_box_group()); ?>
                              </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="paymentType_id" class="col-lg-2 control-label">Greiðslumáti</label>
                          <div class="col-lg-10">
                              <select class="form-control" id="paymentType_id" name="paymentType_id" required>
                                  <?php print UTF8_encode($newSQL->combo_box_paymentType()); ?>
                              </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="subscriptionType_id" class="col-lg-2 control-label">Tegund áskriftar</label>
                          <div class="col-lg-10">
                              <select class="form-control" id="subscriptionType_id" name="subscriptionType_id" required>
                                  <?php print UTF8_encode($newSQL->combo_box_subscriptionType()); ?>
                              </select>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="inputDate" class="col-lg-2 control-label"> Dagsetning kaupa </label>
                          <div class="col-lg-10">
                              <input type="date" class="form-control" id="begin_date" name="begin_date" value="<?php echo date('Y-m-d') ?>" placeholder="" required>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="inputDate" class="col-lg-2 control-label"> Gildir til</label>
                          <div class="col-lg-10">
                              <input type="date" class="form-control" id="end_date" name="end_date" placeholder="" required>
                          </div>
                      </div>
                      <div class="form-group">
                          <div class="col-lg-10 col-lg-offset-2">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                              <button type="reset" class="btn btn-danger">Hreinsa</button>
                              <button type="submit" name="addSubscription" class="btn btn-primary">Kaupa áskrift</button>
                          </div>
                      </div>
                  </fieldset>
              </form>
          </div>
      </div>
    </div>
  </div>
    <!--  Add Contact modal -->
    <div class="modal fade" id="addContactModal" tabindex="-1" role="dialog" aria-labelledby="addContactLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addContactLabel">Bæta við tengilið</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" id="addContact" method="POST" action="">
                        <fieldset>
                            <input type="hidden" name="action" value="addContact" />
                            <input type="hidden" class="form-control" id="boxerID" name="boxer_id" value="<?php echo $id;?>" />
                            <div class="form-group required">
                                <label for="contactName" class="col-lg-2 control-label">Nafn</label>
                                <div class="col-lg-10">
                                    <input type="text" class="form-control" id="contactName" name="name" placeholder="Jón Jónsson" required>
                                </div>
                            </div>
                            <div class="form-group required">
                                <label for="contactPhone" class="col-lg-2 control-label">Sími</label>
                                <div class="col-lg-10">
                                    <input type="text" class="form-control" id="contactPhone" name="phone" placeholder="5551234" required>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="contactEmail" class="col-lg-2 control-label">Netfang</label>
                                <div class="col-lg-10">
                                    <input type="email" class="form-control" id="contactEmail" name="email" placeholder="some@mail.com">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-10 col-lg-offset-2">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="reset" class="btn btn-danger">Hreinsa</button>
                                    <button type="submit" name="addContact" class="btn btn-primary">Skrá tengilið</button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
  <!-- Update boxer-info modal -->
  <div class="modal fade" id="updateInfo" tabindex="-1" role="dialog" aria-labelledby="updateInfoLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="updateInfoLabel"><strong><i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i> Uppfæra Notanda</strong></h4>
        </div>
          <?php $info = $newSQL->list_full_boxer_info($id); ?>
          <div class="modal-body">
              <form class="form-horizontal" id="updateBoxer" method="POST" action="">
                  <fieldset>
                      <input type="hidden" name="action" value="updateBoxer" />
                      <input type="hidden" class="form-control" id="boxerID" name="boxer_id" value="<?php echo $id;?>" />
                      <div class="form-group required">
                          <label for="inputName" class="col-lg-2 control-label">Nafn</label>
                          <div class="col-lg-9">
                              <input type="text" class="form-control" id="inputName" name="name" value="<?php echo utf8_encode($info['Name']); ?>" placeholder="Name of Boxer" required />
                          </div>
                      </div>
                      <div class="form-group required">
                          <label for="inputSSN" class="col-lg-2 control-label">Kennitala</label>
                          <div class="col-lg-9">
                              <input type="number" class="form-control" id="inputSSN" name="kt" value="<?php echo utf8_encode($info['kt']); ?>" maxlength="10"  placeholder="10 digit Icelandic Kennitala" pattern="((0[1-9])|([1-2][0-9])|(3[01]))((0[1-9])|(1[0-2]))([0-9]{2})[0-9]{4}" required/>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="inputPhone" class="col-lg-2 control-label">Sími</label>
                          <div class="col-lg-9">
                              <input type="tel" class="form-control" id="inputPhone" name="phone" value="<?php echo utf8_encode($info['phone']); ?>" placeholder="7 digit Icelandic phone number, f.e. 5552233" />
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="inputEmail" class="col-lg-2 control-label">Netfang</label>
                          <div class="col-lg-9">
                              <input type="email" class="form-control" id="inputEmail" name="email" value="<?php echo utf8_encode($info['email']); ?>" placeholder="valid email address, f.e. user@hfh.is"/>
                          </div>
                      </div>
                      <div class="form-group">
                          <label for="inputEmail" class="col-lg-2 control-label">Rfid</label>
                          <div class="col-lg-9">
                              <input type="text" class="form-control" id="inputRfid" name="rfid" value="<?php echo utf8_encode($info['rfid']); ?>" placeholder="RFID identity, 10 digit number"/>
                          </div>
                      </div>
                      <div class="form-group required">
                          <label class='col-lg-5 col-lg-offset-1 control-label'>
                              Fylla þarf út reyti merkta
                          </label>
                      </div>

                      <div class="form-group">
                          <div class="col-lg-10 col-lg-offset-2">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                              <button type="reset" class="btn btn-danger">Hreinsa</button>
                              <button type="submit" name="updateBoxer" class="btn btn-primary">Uppfæra Iðkanda</button>
                          </div>
                      </div>
                  </fieldset>
              </form>
          </div>
      </div>
    </div>
  </div>
<!-- Scripts ---->
<script>
    $(document).ready(function() {
        $('#subscription_info').DataTable();
        $('#checkin_info').DataTable();
    });

    function imageUpload(error, data, response) {
        if (error == null) {
            $.ajax({
                type: 'POST',
                url: 'user.php',
                data: {
                    'action' : 'updateImage',
                    // Getting the location, splitting it at ? with search, splitting the result at = and getting the righthandside (boxerID)
                    'boxer_id' : window.location.search.split("=")[1],
                    'path' : response.file
                }
            }).done(function(result) {
                var jsonReturn = JSON.parse(result);
                alertify.logPosition("top right");
                alertifyType = jsonReturn.status;
                if(alertifyType == 'success'){
                    alertify.success(jsonReturn.msg);
                } else if(alertifyType == 'error') {
                    alertify.error(jsonReturn.msg);
                }
            }).fail(function() {
                alertify.logPosition("top right");
                alertify.error("Something went wrong, please try again later");
            });
        }
    }

  // Adding a subscription to a specific boxer
  $('form#addSubscription').on('submit', function() {
      var form = $(this);
      event.preventDefault();
      var data = form.serialize();
      $.ajax({
          url: form.attr('action'),
          data: data,
          method:'POST',
          success: function(result) {
              var jsonReturn = JSON.parse(result);
              alertifyType = jsonReturn.status;
              if(alertifyType == 'success'){
                  alertify.success(jsonReturn.msg);
                  var t = $('#subscription_info').DataTable();
                  t.row.add( [
                      jsonReturn.info[2],
                      jsonReturn.info[3],
                      jsonReturn.info[4],
                      jsonReturn.info[5],
                      jsonReturn.info[6]
                  ] ).draw( false );
                  $('form#addSubscription')[0].reset();
                  //$('#addSubscriptionModal').modal('hide');
              } else if(alertifyType == 'error') {
                  alertify.error(jsonReturn.msg);
              }
          }
      });
  });

  // Adding a comment to a boxer
  $('form#addComment').on('submit', function() {
      var form = $(this);
      event.preventDefault();
      var data = form.serialize();
      $.ajax({
          url: form.attr('action'),
          data: data,
          method:'POST',
          success: function(result) {
              console.log(result);
              var jsonReturn = JSON.parse(result);
              alertifyType = jsonReturn.status;
              alertify.logPosition("top right");
              if(alertifyType == 'success'){
                  alertify.logPosition("top right");
                  alertify.log(jsonReturn.msg);
                  location.reload();
                  $('form#addComment')[0].reset();
                  $('#comments').append('<div class="well well-sm">' + jsonReturn.comment + '<span class="label pull-right">' + jsonReturn.added_by + ' (' + jsonReturn.date +')</span></div>');
              } else if(alertifyType == 'error') {
                  alertify.error(jsonReturn.msg);
              }
          }
      });
  });

  // Adding a contact to a boxer
  $('form#addContact').on('submit', function() {
      var form = $(this);
      event.preventDefault();
      var data = form.serialize();
      $.ajax({
          url: form.attr('action'),
          data: data,
          method:'POST',
          success: function(result) {
              //console.log(result);
              var jsonReturn = JSON.parse(result);
              alertifyType = jsonReturn.status;
              alertify.logPosition("top right");
              if(alertifyType == 'success'){
                  alertify.logPosition("top right");
                  alertify.log(jsonReturn.msg);
                  $('form#addContact')[0].reset();
                  $('#contacts').append('<div class="panel-group"><div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title">'
                        + '<a data-toggle="collapse" href="#collapse'+ jsonReturn.id +'">' + jsonReturn.name + '</a></h4></div>'
                        + '<div id="collapse' + jsonReturn.id + '" class="panel-collapse collapse"> <ul class="list-group">'
                        + '<li class="list-group-item">' + jsonReturn.phone + '</li><li class="list-group-item">' + jsonReturn.email + '</li></ul></div></div></div>'
                  );
                  //$('#addContactModal').modal('hide');
              } else if(alertifyType == 'error') {
                  alertify.error(jsonReturn.msg);
              }
          }
      });
  });
  // Update a boxer
  $('form#updateBoxer').on('submit', function() {
      var form = $(this);
      event.preventDefault();
      var data = form.serialize();
      $.ajax({
          url: form.attr('action'),
          data: data,
          method:'POST',
          success: function(result) {
              //console.log(result);
              var jsonReturn = JSON.parse(result);
              alertifyType = jsonReturn.status;
              alertify.logPosition("top right");
              if(alertifyType == 'success'){
                  alertify.logPosition("top right");
                  alertify.log(jsonReturn.msg);
                  document.getElementById('boxerInfo').innerHTML = '<div class="panel panel-success">'
                      + '<div class="panel-heading">' + jsonReturn.name + '</div>'
                      + '<div class="panel-body" id="infoKT"><strong>kt: </strong>' + jsonReturn.kt + '</div>'
                      + '<div class="panel-body"><strong>S&iacute;mi: </strong>' + jsonReturn.phone + '</div>'
                      + '<div class="panel-body"><strong>Veffang: </strong>' + jsonReturn.email + '</div>'
                      + ( jsonReturn.rfid != '' ? '<div class="panel-body"><strong>rfid: </strong>' + jsonReturn.rfid + '</div>' : '')
                      + '</div>';
                  //$('#updateBoxerModal').modal('hide');
              } else if(alertifyType == 'error') {
                  alertify.error(jsonReturn.msg);
              }
          }
      });
  });
</script>

<?php
else :
    $pageTitle = "Greiðsluyfirlit";
    include_once (fullDirPath . "/head.php");
    include_once (fullDirPath . "/scripts.php");
    echo '<div class="modal show" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" onclick="goBack()" aria-label="GoBack"><span aria-hidden="true">&laquo;</span></button>
                        <h4 class="modal-title" id="errorLabel">Villa hefur komið upp </h4>
                        <p class="text-danger"> Ekki náðist að sækja upplýsingar um notandann </p>
                    </div>
                </div>
              </div>
            </div>';
      echo "<script>
                function goBack() {
                    window.history.back();
                }
            </script>";
endif;
?>
