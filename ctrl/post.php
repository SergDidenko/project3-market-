<?php
$user=$session->has('user') ? $session->get('user') : null;
$u_repo=$em->getRepository('User');
$u=$u_repo->findOneBy(['username'=>$user]);
$admin=$u==null ? null : $u->getAdmin();
$err_obj=[];
$path=extract($matcher->match($path), EXTR_SKIP);
if ($crud!==null && $user!==null) {
	if ($crud==="delete" && $id!==null) {
		$query=$em->createQueryBuilder()
				  ->select('p')
				  ->from('Post', 'p')
				  ->where('p.id=?1')
				  ->setParameter(1, $id)
				  ->getQuery();
		$post=$query->getSingleResult();
		$username=$post->getUser();
		if ($user===$username->getUsername()) {
			$em->remove($post);
			$em->flush();
			$redirect->send();
		}else{
			$redirect->send();
		}
	}elseif ($crud==="update" && $id!==null) {
		$query=$em->createQueryBuilder()
				  ->select('p')
				  ->from('Post', 'p')
				  ->where('p.id=?1')
				  ->setParameter(1, $id)
				  ->getQuery();
		$post=$query->getSingleResult();
		$username=$post->getUser();
		if ($user===$username->getUsername()) {
			if ($r->getMethod()==="POST") {
				if ($r->request->has('title') && $r->request->has('content')) {
					$title=htmlentities($r->request->get('title'));
					$content=htmlentities($r->request->get('content'));
					$post->setTitle($title)->setContent($content);
					$e=$validator->validate($post);
					if ($e->has(0)) {
						foreach ($e as $k => $error) {
							$err_obj[$e->get($k)->getPropertyPath()][]=$e->get($k)->getMessage();
						}
					}else{	
						if ($r->files->get('upload')!==null) {
							$upload=$r->files->get('upload');
							$post->setImageName($upload->getClientOriginalName())->setImagePath($upload->move('files')->getPathName());
						}
						$em->persist($post);
						$em->flush();
						$redirect->send();
					}
				}
			}	
		}else{
			$redirect->send();
		}
		echo $env->render('_post_form.twig', ['button'=>'Update', 'title'=>'Update form','user'=>$user, 'admin'=>$admin, 'post'=>$post, 'err_obj'=>$err_obj]);
	}elseif($crud==="create" && $user!==null){
		if ($r->getMethod()==="POST") {
			if ($r->request->has('title') && $r->request->has('content') && $r->files->has('upload')) {
				$title=htmlentities($r->request->get('title'));
				$content=htmlentities($r->request->get('content'));
				$upload=$r->files->get('upload');
				$post=new Post;
				$post->setTitle($title)->setContent($content)->setImageName($upload->getClientOriginalName())->setImagePath($upload->move('files')->getPathName());
				$e=$validator->validate($post);
				if ($e->has(0)) {
					foreach ($e as $k => $error) {
						$err_obj[$e->get($k)->getPropertyPath()][]=$e->get($k)->getMessage();
					}
				}else{
					$post->setUser($u);
					$em->persist($post);
					$em->flush();
					$redirect->send();
				}
			}
		}
		echo $env->render('_post_form.twig', ['button'=>'Create', 'title'=>'Create post', 'err_obj'=>$err_obj, 'user'=>$user, 'admin'=>$admin]);
	}elseif ($crud==="separate" && $id!==null) {
		$query=$em->createQueryBuilder()
				  ->select('p')
				  ->from('Post', 'p')
				  ->where('p.id=?1')
				  ->setParameter(1, $id)
				  ->getQuery();
		$posts=$query->getResult();
		echo $env->render('_post.twig', ['user'=>$user, 'admin'=>$admin, 'posts'=>$posts]);
	}
}else{

	$query=$em->createQueryBuilder()
			  ->select('p')
			  ->from('Post', 'p')
			  ->orderBy('p.createAt') //from old to new post
			  ->getQuery();
	$posts=$query->getResult();
	$total=count($posts);
	$pager=new Pager($total, $page);
	
	$max=$pager->getMax();
	$start=$pager->getStart();
	$posts=array_slice($posts, $start, $max);
	
	echo $env->render('_post.twig', ['user'=>$user, 'admin'=>$admin, 'posts'=>$posts, 'page'=>$page]);
	$maxpage=$pager->getMaxpage();
	$links=$pager->getLinks();
	echo $env->render('_pager_post.twig', ['page'=>$page, 'links'=>$links, 'maxpage'=>$maxpage]);
}