<?php
$user=$session->has('user') ? $session->get('user') : null;
$u_repo=$em->getRepository('User');
$u=$u_repo->findOneBy(['username'=>$user]);
$admin=$u==null ? null : $u->getAdmin();
$query=$em->createQueryBuilder()
			  ->select('c')
			  ->from('Category', 'c')
			  ->getQuery();
$categories=$query->getResult();
$path=extract($matcher->match($path), EXTR_SKIP);
if ($name!==null) {
	$repo=$em->getRepository('Product');
	$query=$repo->createQueryBuilder('p')
			  ->select('p')
			  ->innerJoin('p.categories', 'c')
			  ->where('c.name=?1')
			  ->setParameter(1,$name)
			  ->getQuery();
	$products=$query->getResult();
	$total=count($products);
	$pager=new Pager($total, $page);
	$products=array_slice($products, $pager->getStart(), $pager->getMax());
	echo $env->render('_home.twig', ['user'=>$user, 'admin'=>$admin, 'categories'=>$categories, 'products'=>$products, 'page'=>$page]);
}else{
	$query=$em->createQueryBuilder()
			  ->select('p')
			  ->from('Product', 'p')
			  ->getQuery();
	$products=$query->getResult();
	$total=count($products);
	$pager=new Pager($total, $page);
	$products=array_slice($products, $pager->getStart(), $pager->getMax());
	echo $env->render('_home.twig', ['user'=>$user, 'admin'=>$admin, 'categories'=>$categories, 'products'=>$products, 'page'=>$page]);
}
$maxpage=$pager->getMaxpage();
$links=$pager->getLinks();
echo $env->render('_pager.twig', ['maxpage'=>$maxpage, 'page'=>$page, 'links'=>$links, 'name'=>$name]);
