<?php
declare(strict_types=1);

class HomeController extends Controller
{
    public function index(): void
    {
        $memoires = Memoire::search([], null);
        $topViewed = Memoire::topViewed(6);
        $topRated = Memoire::topRated(6);
        $this->view('home/index', compact('memoires', 'topViewed', 'topRated'));
    }
}
