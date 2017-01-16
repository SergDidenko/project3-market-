<?php
$user=$session->has('user') ? $session->get('user') : null;
$u_repo=$em->getRepository('User');
$u=$u_repo->findOneBy(['username'=>$user]);
$admin=$u==null ? null : $u->getAdmin();
$err_obj=[];
if ($admin!=='root') {
	$redirect->send();
}
$path=extract($matcher->match($path), EXTR_SKIP);
if ($crud==="delete" && $id!==null) {
	$query=$em->createQueryBuilder()
			  ->select('c')
			  ->from('Category', 'c')
			  ->where('c.id=?1')
			  ->setParameter(1, $id)
			  ->getQuery();
	$category=$query->getSingleResult();
	$em->remove($category);
	$em->flush();
	$redirect->send();
}elseif ($crud==="update" && $id!==null) {
	$query=$em->createQueryBuilder()
			  ->select('c')
			  ->from('Category', 'c')
			  ->where('c.id=?1')
			  ->setParameter(1, $id)
			  ->getQuery();
	$category=$query->getSingleResult();
	if ($r->getMethod()==="POST") {
		if ($r->request->has('name')) {
			$name=$r->request->get('name');
			$category->setName($name);
			$e=$validator->validate($category);
			if ($e->has(0)) {
				foreach ($e as $k => $error) {
					$err_obj[$e->get($k)->getPropertyPath()][]=$e->get($k)->getMessage();
				}
			}else{
				$em->flush();
				$redirect->send();
			}
		}
	}
	echo $env->render('_category_form.twig', ['user'=>$user, 'admin'=>$admin, 'button'=>'Update', 'title'=>'Update form', 'err_obj'=>$err_obj, 'category'=>$category]);
}else{
	if ($r->getMethod()==="POST") {
		if ($r->request->has('name')) {
			$name=htmlentities($r->request->get('name'));
			$query=$em->createQueryBuilder()
					  ->select('c')
					  ->from('Category', 'c')
					  ->where('c.name=?1')
					  ->setParameter(1, $name)
					  ->getQuery();
			$c=$query->getResult();
			if ($c!==[]) {
				$err_obj['name'][]="This name was defined earlier. Please use other name.";
			}else{
				$category=new Category;
				$category->setName($name);
				$e=$validator->validate($category);
				if ($e->has(0)) {
					foreach ($e as $k => $error) {
						$err_obj[$e->get($k)->getPropertyPath()][]=$e->get($k)->getMessage();
					}
				}else{
					$em->persist($category);
					$em->flush();
					$redirect->send();
				}
			}
		}
	}
	echo $env->render('_category_form.twig', ['button'=>'Create', 'title'=>'Create category', 'user'=>$user, 'admin'=>$admin, 'err_obj'=>$err_obj]);
}