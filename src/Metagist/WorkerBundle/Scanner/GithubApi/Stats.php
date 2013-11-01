<?php
namespace Metagist\WorkerBundle\Scanner\GithubApi;

use Github\Api\AbstractApi;

/**
 * Provides Github repo statistics.
 * 
 * @link   http://developer.github.com/v3/repos/statistics/
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Stats extends AbstractApi
{
    /**
     * @link http://developer.github.com/v3/repos/statistics/#get-contributors-list-with-additions-deletions-and-commit-counts
     *
     * @param string $username
     * @param string $repository
     *
     * @return array
     */
    public function contributors($username, $repository)
    {
        return $this->get('repos/'.urlencode($username).'/'.urlencode($repository).'/stats/contributors');
    }
}