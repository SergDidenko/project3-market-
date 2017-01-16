<?php
$user=$session->has('user') ? $session->get('user') : null;
$u_repo=$em->getRepository('User');
$u=$u_repo->findOneBy(['username'=>$user]);
$admin=$u==null ? null : $u->getAdmin();
if ($admin!=='root') {
	$redirect->send();
}
$err_obj=[];
$query=$em->createQueryBuilder()
			  ->select('c')
			  ->from('Category', 'c')
			  ->getQuery();
$categories=$query->getResult();
$path=extract($matcher->match($path), EXTR_SKIP);
if ($crud!==null && $id!==null) {
	if ($crud==="delete") {
		$query=$em->createQueryBuilder()
				  ->select('p')
				  ->from('Product', 'p')
				  ->where('p.id=?1')
				  ->setParameter(1, $id)
				  ->getQuery();
		$product=$query->getSingleResult();
		$em->remove($product);
		$em->flush();
		$redirect->send();
	}elseif($crud==="update"){
		$repo=$em->getRepository('Product');
		$query=$repo->createQueryBuilder('p')
					->select('p','c')
					->innerJoin('p.categories', 'c')
					->where('p.id=?1')
					->setParameter(1, $id)
					->getQuery();
		$product=$query->getSingleResult();
		$cat_old=$query->getArrayResult()[0]['categories'];
		//array of categories for my product
		if ($r->getMethod()==="POST") {
			if ($r->request->has('name') && $r->request->has('category')) {
				$name=$r->request->get('name');
				$category=$r->request->get('category');
				foreach ($category as $k => $v) {
					$query=$em->createQueryBuilder()
							  ->select('c')
							  ->from('Category', 'c')
							  ->where('c.id=?1')
							  ->setParameter(1, $v)
							  ->getQuery();
					$cat[]=$query->getSingleResult();
				}
				$repo=$em->getRepository('Category');
				foreach ($cat_old as $k => $v) {
					$c=$repo->findOneBy(['id'=>$v['id']]);
					$product->removeCategory($c);
				}
				if ($name!==$product->getName()) {
					$product->setName($name);
				}
				foreach ($cat as $k => $v) {
						$product->addCategory($v);
						$v->addProduct($product);
				}
				$em->persist($product);
				$em->flush();
				$redirect->send();
			}
		}
		echo $env->render('_product_form.twig', ['button'=>'Update', 'title'=>'Update product', 'user'=>$user, 'admin'=>$admin, 'err_obj'=>$err_obj, 'categories'=>$categories, 'product'=>$product]);
	}
}else{
	if ($r->getMethod()==="POST") {
		if ($r->request->has('name') && $r->request->has('category')) {
			$name=htmlentities($r->request->get('name'));
			$query=$em->createQueryBuilder()
					  ->select('p')
					  ->from('Product', 'p')
					  ->where('p.name=?1')
					  ->setParameter(1, $name)
					  ->getQuery();
			$p=$query->getResult();
			if ($p!==[]) {
				$err_obj['name'][]="This name was defined earlier. Please use other name.";
			}else{
				$product=new Product;
				$product->setName($name);
				$category=$r->request->get('category');
				foreach ($category as $k => $v) {
					$query=$em->createQueryBuilder()
							  ->select('c')
							  ->from('Category', 'c')
							  ->where('c.id=?1')
							  ->setParameter(1, $v)
							  ->getQuery();
					$cat[]=$query->getSingleResult();
				}
				$e=$validator->validate($product);
				if ($e->has(0)) {
					foreach ($e as $k => $error) {
						$err_obj[$e->get($k)->getPropertyPath()][]=$e->get($k)->getMessage();
					}
				}else{
					foreach ($cat as $k => $v) {
						$product->addCategory($v);
						$v->addProduct($product);
					}
					$em->persist($product);
					$em->flush();
					$redirect->send();
				}
			}
		}
	}
	
	echo $env->render('_product_form.twig', ['button'=>'Create', 'title'=>'Create product', 'user'=>$user, 'admin'=>$admin, 'err_obj'=>$err_obj, 'categories'=>$categories]);
}
