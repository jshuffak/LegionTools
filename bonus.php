<!DOCTYPE html>
<html>
<head>
  <title>Bonus Workers</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script type="text/javascript" src="Retainer/scripts/gup.js"></script>
  <script type="text/javascript" src="Retainer/scripts/bonusWorker.js"></script>
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
</head>
<body style = "width:500px; margin-left:auto; margin-right:auto;"> 
  <h1>Bonus a worker:</h1>
  <form role="form">
    <div class="form-group">
      <label for="workerId">Worker Id</label>
      <input name = "workerId" class="form-control" id="workerId" placeholder="Worker Id (e.g. A33FSISZ9EBTS)">
    </div>
    <div class="form-group">
      <label for="assignmentId">Assignment Id</label>
      <input name = "id" class="form-control" id="assignmentId" placeholder="Assignment Id">
    </div>
    <div class="form-group">
      <label for="reason">Reason</label>
      <input name = "reason" class="form-control" id="reason" value="Did extra work.">
    </div>
    <div class="form-group">
      <label for="bonusAmount">Bonus amount (in dollars)</label>
      <input name = "amount" class="form-control" id="bonusAmount" placeholder="0.01">
    </div>
    <div class="form-group">
      <label for="accessKey">Access Key</label>
      <input name = "accessKey" class="form-control" id="accessKey" placeholder="AMT Access Key">
    </div>
    <div class="form-group">
      <label for="secretKey">Secret Key</label>
      <input name = "secretKey" class="form-control" id="secretKey" placeholder="AMT Secret Key">
    </div>
    <div class="form-group">
      <label for="Use Sandbox?">Use Sandbox?</label>
      <input name = "useSandbox" class="form-control" id="useSandbox" placeholder="true or false">
    </div>
    <!-- <input type="hidden" name="useSandbox" value="true"> -->
    <input type="hidden" name="operation" value="Bonus">
    <button type="submit" class="btn btn-danger">Give bonus to the worker</button>
  </form>

  <p><div id="bonusedHistory"></div></p>


</body>
</html>
