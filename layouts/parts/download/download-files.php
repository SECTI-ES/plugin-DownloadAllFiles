<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('download-files');
?>

<download-files :entity='<?php echo json_encode($entity); ?>'></download-files>