<?php

namespace litepul\plugins\tickets;

class Factory extends \litepubl\post\Factory
{

public function getPosts()
{
return Tickets::i();
}

}