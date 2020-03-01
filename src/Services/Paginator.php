<?php
namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use App\Exception\PaginationBadRequestException;

class Paginator
{
    public function paginateFromRequest(&$queryBuilder, Request $request) {
        $pagination = $request->query->get('pagination');
            
        // page and size
        if($pagination) {
            if(array_key_exists('page', $pagination) && is_numeric($pagination['page']) && $pagination['page'] > 0 && 
                array_key_exists('size', $pagination) && is_numeric($pagination['size']) && $pagination['size'] > 0) {
                
                $page = (int)$pagination['page'];
                $size = (int)$pagination['size'];
                
                return $this->paginatePage($queryBuilder, $page, $size);

            } else  {
                throw new PaginationBadRequestException();
            }
        } 
    }

    public function paginatePage(&$queryBuilder, $page, $size) {
        if(is_numeric($page) && $page > 0 && is_numeric($size) && $size > 0) {
            $offset = $this->getOffset($page, $size);
            $limit = $this->getLimit($page, $size);

            $queryBuilder
                ->offset($offset)
                ->limit($limit)
            ;

            $metadata = [
                'page' => $page,
                'size' => $size
            ];

            return $metadata;
        } else {
            throw new PaginationBadRequestException();
        }
    }


    public function __call($method, $arguments) {
        if($method == 'paginate') {
            if(count($arguments) == 2) {
               return call_user_func_array(array($this,'paginateFromRequest'), $arguments);
            }
            else if(count($arguments) == 3) {
               return call_user_func_array(array($this,'paginatePage'), $arguments);
            }
        }
     } 

    public function getOffset($page, $size){
        return ($page-1) * $size;
    }

    public function getLimit($page, $size){
        return $size;
    }
}
