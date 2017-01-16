<?php
$user=$session->has('user') ? $session->get('user') : null;
$u_repo=$em->getRepository('User');
$u=$u_repo->findOneBy(['username'=>$user]);
$admin=$u==null ? null : $u->getAdmin();
$err_obj=[];
$path=extract($matcher->match($path), EXTR_SKIP);
if ($user==null) {
	if ($action==="registration") {
		if ($r->getMethod()==="POST") {
			if ($r->request->has('username') && $r->request->has('password')) {
				$username=htmlentities($r->request->get('username'));
				$password=htmlentities($r->request->get('password'));
				$user=new User;
				$user->setUsername($username)->setPassword($password);
				$e=$validator->validate($user);
				if ($e->has(0)) {
					foreach ($e as $k => $error) {
						$err_obj[$e->get($k)->getPropertyPath()][]=$e->get($k)->getMessage();
					}
				}else{
					$query=$em->createQueryBuilder()
							  ->select('u')
							  ->from('User', 'u')
							  ->getQuery();
					$users=$query->getResult();
					if ($users===[]) {
						$admin="root";
					}else{
						$admin="user";
					}
					$user->setPassword(password_hash($password,1))->setAdmin($admin);
					$em->persist($user);
					$em->flush();
					$redirect->send();
				}
			}
		}
		echo $env->render('_user_form.twig', ['button'=>'Register', 'title'=>'Registration form', 'err_obj'=>$err_obj, 'user'=>$user, 'admin'=>$admin]);
	}elseif ($action==="login") {
		if ($r->getMethod()==="POST") {
			if ($r->request->has('username') && $r->request->has('password')) {
				$username=htmlentities($r->request->get('username'));
				$password=htmlentities($r->request->get('password'));
				$u=new User;
				$u->setUsername($username)->setPassword($password);
				$e=$validator->validate($u);
				if ($e->has(0)) {
					foreach ($e as $k => $error) {
						$err_obj[$e->get($k)->getPropertyPath()][]=$e->get($k)->getMessage();
					}
				}else{
					$query=$em->createQueryBuilder()
							  ->select('u')
							  ->from('User', 'u')
							  ->where('u.username=?1')
							  ->setParameter(1, $username)
							  ->getQuery();
					$user=$query->getSingleResult();
					if (empty($user)) {
						echo "User invalid";
					}else{
						if (password_verify($password, $user->getPassword())) {
							$session->set('user', $username);
							$redirect->send();
						}else{
							echo "Password incorrect. Please try again...";
						}
					}
				}
			}
		}
		echo $env->render('_user_form.twig', ['button'=>'Login', 'title'=>'Login form', 'err_obj'=>$err_obj, 'user'=>$user, 'admin'=>$admin]);
	}
}else{
	if ($action==="logout") {
		$session->remove('user');
	}
	$redirect->send();
}


