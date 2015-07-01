<?php


namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

class Decklists
{
    public function __construct(EntityManager $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    

    /**
     * returns the list of decklist favorited by user
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function favorites ($user_id, $start = 0, $limit = 30)
    {
    
    
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();
    
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join favorite f on f.decklist_id=d.id
                left join tournament t on d.tournament_id=t.id
                where f.user_id=?
                order by creation desc
                limit $start, $limit", array(
                        $user_id
                ))
                ->fetchAll(\PDO::FETCH_ASSOC);
    
        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
        return array(
                "count" => $count,
                "decklists" => $rows
        );
    
    }
    
    /**
     * returns the list of decklists published by user
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function by_author ($user_id, $start = 0, $limit = 30)
    {
    
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();
    
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                left join tournament t on d.tournament_id=t.id
                where d.user_id=?
                order by creation desc
                limit $start, $limit", array(
                        $user_id
                ))->fetchAll(\PDO::FETCH_ASSOC);
    
                $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
                return array(
                        "count" => $count,
                        "decklists" => $rows
                );
    
    }
    
    /**
     * returns the list of recent decklists with large number of votes
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function popular ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();
    
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments,
                DATEDIFF(CURRENT_DATE, d.creation) nbjours
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                left join tournament t on d.tournament_id=t.id
                where d.creation > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                order by 2*nbvotes/(1+nbjours*nbjours) DESC, nbvotes desc, nbcomments desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);
    
        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
        return array(
                "count" => $count,
                "decklists" => $rows
        );
    
    }
    
    /**
     * returns the list of decklists with most number of votes
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function halloffame ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();
    
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                left join tournament t on d.tournament_id=t.id
                where nbvotes > 10
                order by nbvotes desc, creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);
    
        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
        return array(
                "count" => $count,
                "decklists" => $rows
        );
    
    }
    
    /**
     * returns the list of decklists with large number of recent comments
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function hottopics ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();
    
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments,
                (select count(*) from comment where comment.decklist_id=d.id and DATEDIFF(CURRENT_DATE, comment.creation)<1) nbrecentcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                left join tournament t on d.tournament_id=t.id
                where d.nbcomments > 1
                order by nbrecentcomments desc, creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);
    
        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
        return array(
                "count" => $count,
                "decklists" => $rows
        );
    
    }

    /**
     * returns the list of decklists with a non-null tournament
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function tournaments ($start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();
    
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                left join tournament t on d.tournament_id=t.id
                where d.tournament_id is not null
                order by creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);
    
        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
        return array(
                "count" => $count,
                "decklists" => $rows
        );
    
    }
    
    /**
     * returns the list of decklists of chosen faction
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function faction ($faction_code, $start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();
    
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join faction f on d.faction_id=f.id
                left join tournament t on d.tournament_id=t.id
                where f.code=?
                order by creation desc
                limit $start, $limit", array(
                        $faction_code
                ))->fetchAll(\PDO::FETCH_ASSOC);
    
                $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
                return array(
                        "count" => $count,
                        "decklists" => $rows
                );
    
    }
    
    /**
     * returns the list of decklists of chosen datapack
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function lastpack ($pack_code, $start = 0, $limit = 30)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();
    
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                where p.code=?
                order by creation desc
                limit $start, $limit", array(
                        $pack_code
                ))->fetchAll(\PDO::FETCH_ASSOC);
    
                $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
                return array(
                        "count" => $count,
                        "decklists" => $rows
                );
    
    }
    
    /**
     * returns the list of recent decklists
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function recent ($start = 0, $limit = 30, $includeEmptyDesc = true)
    {
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();
    
        $additional_clause = $includeEmptyDesc ? "" : "and d.rawdescription!=''";
        
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                p.name lastpack,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                left join tournament t on d.tournament_id=t.id
                where d.creation > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                $additional_clause
                order by creation desc
                limit $start, $limit")->fetchAll(\PDO::FETCH_ASSOC);
    
        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
        return array(
                "count" => $count,
                "decklists" => $rows
        );
    
    }
    


    /**
     * returns a list of decklists according to search criteria
     * @param integer $limit
     * @return \Doctrine\DBAL\Driver\PDOStatement
     */
    public function find ($start = 0, $limit = 30, Request $request)
    {
    
        /* @var $dbh \Doctrine\DBAL\Driver\PDOConnection */
        $dbh = $this->doctrine->getConnection();

        $cardRepository = $this->doctrine->getRepository('AppBundle:Card');
    
        $cards_code = $request->query->get('cards');
        if(!is_array($cards_code)) {
            $cards_code = array();
        }
        $faction_code = filter_var($request->query->get('faction'), FILTER_SANITIZE_STRING);
        $author_name = filter_var($request->query->get('author'), FILTER_SANITIZE_STRING);
        $decklist_title = filter_var($request->query->get('title'), FILTER_SANITIZE_STRING);
        $sort = $request->query->get('sort');
        $packs = $request->query->get('packs');
        
        if(!is_array($packs)) {
            $packs = $dbh->executeQuery("select id from pack")->fetchAll(\PDO::FETCH_COLUMN);
        }
        
        if ($faction_code === "Corp" || $faction_code === "Runner") {
            $side_code = $faction_code;
            unset($faction_code);
        }
    
        $wheres = array();
        $params = array();
        $types = array();
        if (! empty($side_code)) {
            $wheres[] = 's.name=?';
            $params[] = $side_code;
            $types[] = \PDO::PARAM_STR;
        }
        if (! empty($faction_code)) {
            $wheres[] = 'f.code=?';
            $params[] = $faction_code;
            $types[] = \PDO::PARAM_STR;
        }
        if (! empty($author_name)) {
            $wheres[] = 'u.username=?';
            $params[] = $author_name;
            $types[] = \PDO::PARAM_STR;
        }
        if (! empty($decklist_title)) {
            $wheres[] = 'd.name like ?';
            $params[] = '%' . $decklist_title . '%';
            $types[] = \PDO::PARAM_STR;
        }
        if (count($cards_code) ) {
            foreach ($cards_code as $card_code) {
                /* @var $card \AppBundle\Entity\Card */
                $card = $cardRepository->findOneBy(array('code' => $card_code));
                if(!$card) continue;
                
                $wheres[] = 'exists(select * from decklistslot where decklistslot.decklist_id=d.id and decklistslot.card_id=?)';
                $params[] = $card->getId();
                $types[] = \PDO::PARAM_STR;
                
                $packs[] = $card->getPack()->getId();
            }
        }
        if (count($packs)) {
            $wheres[] = 'not exists(select * from decklistslot join card on decklistslot.card_id=card.id where decklistslot.decklist_id=d.id and card.pack_id not in (?))';
            $params[] = array_unique($packs);
            $types[] = \Doctrine\DBAL\Connection::PARAM_INT_ARRAY;
        }
        
        if (empty($wheres)) {
            $where = "d.creation > DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
            $params = array();
            $types = array();
        } else {
            $where = implode(" AND ", $wheres);
        }
    
        $extra_select = "";
        
        switch ($sort) {
            case 'date':
                $order = 'creation';
                break;
            case 'likes':
                $order = 'nbvotes';
                break;
            case 'reputation':
                $order = 'reputation';
                break;
        	case 'popularity':
            default:
        	    $order = 'popularity';
        		$extra_select = '(d.nbvotes/(1+DATEDIFF(CURRENT_TIMESTAMP(),d.creation)/10)) as popularity,';
        		break;
        }
    
        $rows = $dbh->executeQuery(
                "SELECT SQL_CALC_FOUND_ROWS
                d.id,
                d.name,
                d.prettyname,
                d.creation,
                d.user_id,
                d.tournament_id,
                t.description tournament,
                $extra_select
                u.username,
                u.faction usercolor,
                u.reputation,
                u.donation,
                c.code,
                c.title identity,
                d.nbvotes,
                d.nbfavorites,
                d.nbcomments
                from decklist d
                join user u on d.user_id=u.id
                join side s on d.side_id=s.id
                join card c on d.identity_id=c.id
                join pack p on d.last_pack_id=p.id
                join faction f on d.faction_id=f.id
                left join tournament t on d.tournament_id=t.id
                where $where
                order by $order desc
                limit $start, $limit", $params, $types)->fetchAll(\PDO::FETCH_ASSOC);
    
        $count = $dbh->executeQuery("SELECT FOUND_ROWS()")->fetch(\PDO::FETCH_NUM)[0];
    
        return array(
                "count" => $count,
                "decklists" => $rows
        );
    
    }
    
    
    
}
