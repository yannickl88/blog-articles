<?php
$helper = require __DIR__ . '/cli-config.php';

$em = $helper->get('em')->getEntityManager();

$contract = new Contract(new \DateTime('2017-10-10 10:10:00'));
$contract->renew(new \DateInterval('P6M'));
$contract->renew(new \DateInterval('P6M'));
$entityManager->persist($contract);

$contract = new \VersionedContract(new \DateTime('2017-10-10 10:10:00'));
$contract->renew(new \DateInterval('P6M'));
$contract->renew(new \DateInterval('P6M'));
$entityManager->persist($contract);

$entityManager->flush();
