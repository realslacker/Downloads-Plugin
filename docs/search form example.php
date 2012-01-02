<?php
$page = (isset($_REQUEST['pg']) && !empty($_REQUEST['pg']) ? (int)$_REQUEST['pg'] : 1 );
$offset = ($page - 1) * 10;
$forms = array();
$orderby = 'name';
$showexpired = false;
$showinactive = false;

if ($result = downloadSearch($_REQUEST['q'],10,$offset,$orderby,$showexpired,$showinactive)) {
	$forms = $result['downloads'];
	$count = $result['count'];
	$pages = ceil($count/10);

	$pagiation = '';
	if ($pages > 1) for ($i = 1; $i <= $pages; $i++) $pagiation .= '<a href="'.$this->url().'?q='.$_REQUEST['q'].'&pg='.$i.'">'.($i==$page ? '<strong>'.$i.'</strong>' : $i ).'</a> ';
	if (!empty($pagiation)) $pagiation = '<div class="pagiation">'.($page != 1 ? '<span class="skipback"><a href="'.$this->url().'?q='.$_REQUEST['q'].'&pg=1">&lt;&lt;</a> <a href="'.$this->url().'?q='.$_REQUEST['q'].'&pg='.($page-1).'">&lt;</a></span> ' : '').'<span class="pages">'.$pagiation.'</span>'.($page != $pages ? ' <span class="skipforward"><a href="'.$this->url().'?q='.$_REQUEST['q'].'&pg='.($page+1).'">&gt;</a> <a href="'.$this->url().'?q='.$_REQUEST['q'].'&pg='.$pages.'">&gt;&gt;</a></span>' : '').'</div>';
}
?>
<br />
<div style="text-align:right;" class="noprint">
	<form id="forms-search" action="<?php echo $this->url();?>" method="get">
		<input type="text" name="q" value="<?php echo $_REQUEST['q'];?>" /> <input type="submit" value="Search Forms" />
	</form>
</div>
<?php
if (count($forms) > 0) {
	echo $pagiation;
	foreach ($forms as $form) echo downloadBoxFormat($form);
	echo $pagiation;
}
else {
	echo "<p style=\"text-align:center;margin:20px;\"><strong>No results found!</strong><br />Please try more general search terms or a new query.</p>";
}
?>