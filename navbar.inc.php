<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<a class="navbar-brand" href="<?php echo $system->getAppliUrl() ?>"><?php echo ToolBox::toHtml($system->getAppliName()) ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse d-lg-flex" id="navbarSupportedContent">
    <ul class="navbar-nav flex-lg-fill">
		<li class="nav-item"><a class="nav-link" href="index.php">Montants</a></li>
		<li class="nav-item"><a class="nav-link" href="amount_edit.php">Nouveau montant</a></li>
    </ul>
  </div>
</nav>