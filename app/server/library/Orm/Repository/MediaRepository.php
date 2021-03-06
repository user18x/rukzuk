<?php
namespace Orm\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MediaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */

class MediaRepository extends EntityRepository
{
  /**
   * @param string $websiteId
   * @return integer
   */
  public function getSizeByWebsiteId($websiteId)
  {
    $dql = 'SELECT SUM(m.size) as websitesize FROM Orm\\Entity\\Media m'
      .' WHERE m.websiteid = :websiteid';
    $query = $this->_em->createQuery($dql);
    $query->setParameters(array('websiteid' => $websiteId));

    try {
      $result = $query->getSingleResult();
      return (int)$result['websitesize'];
    } catch (\Exception $e) {
      \Seitenbau\Registry::getLogger()->logException(__METHOD__, __LINE__, $e, \Seitenbau\Log::CRIT);
    }
    return 0;
  }

  /**
   * @param string $id
   * @param string $websiteId
   * @return \Orm\Entity\Media|null
   */
  public function findByIdAndWebsiteId($id, $websiteId)
  {
    $dql = 'SELECT m FROM Orm\\Entity\\Media m WHERE m.id = :id AND m.websiteid = :websiteid';
    $query = $this->_em->createQuery($dql);
    $query->setParameters(array(
      'id' => $id,
      'websiteid' => $websiteId
    ));

    try {
      $result = $query->getSingleResult();
    } catch (\Exception $e) {
      \Seitenbau\Registry::getLogger()->logException(
          __METHOD__,
          __LINE__,
          $e,
          \Seitenbau\Log::NOTICE
      );
      $result = null;
    }

    return $result;
  }

  /**
   * Gibt mehrere Media-Objekte anhand der uebergebenen Ids zurueck
   *
   * @param array   $ids
   * @param string  $websiteId
   * @param array
   */
  public function findMultipleByIds(array $ids, $websiteId)
  {
    $dql = 'SELECT  m FROM Orm\\Entity\\Media m'
      . ' WHERE m.id IN (:ids) '
      . ' AND m.websiteid = :websiteid';
    
    $query = $this->_em->createQuery($dql);
    $query->setParameters(array(
      'ids' => $ids,
      'websiteid' => $websiteId
    ));
    
    try {
      $result = $query->getResult();
    } catch (\Exception $e) {
      \Seitenbau\Registry::getLogger()->logException(
          __METHOD__,
          __LINE__,
          $e,
          \Seitenbau\Log::NOTICE
      );
      $result = null;
    }
    
    return $result;
  }

  /**
   * @param string  $websiteId
   * @param array   $filterValues
   * @param boolean $ignoreLimit
   *
   * @return array
   */
  public function findByWebsiteIdAndFilter($websiteId, array $filterValues, $ignoreLimit = false)
  {
    $dqlParams = array();

    // create dql
    $dql = 'SELECT m FROM Orm\\Entity\\Media m WHERE m.websiteid = :websiteid';
    $dqlParams['websiteid'] = $websiteId;
    if (isset($filterValues['search']) && $filterValues['search'] !== null) {
      $dql .= ' AND m.name LIKE :search';
      $dqlParams['search'] = '%'.trim($filterValues['search']).'%';
    }

    if (isset($filterValues['albumid']) && $filterValues['albumid'] !== null) {
      $dql .= ' AND m.albumid = :albumid';
      $dqlParams['albumid'] = $filterValues['albumid'];
    }

    if (isset($filterValues['type']) && $filterValues['type'] !== null) {
      $dql .= ' AND m.type = :type';
      $dqlParams['type'] = $filterValues['type'];
    }

    if (isset($filterValues['sort']) && $filterValues['sort'] !== null) {
      $entityColumns = $this->getClassMetadata()->getColumnNames();
      if (in_array($filterValues['sort'], $entityColumns)) {
        $dql.= sprintf(" ORDER BY LOWER(m.%s)", $filterValues['sort']);
        if (isset($filterValues['direction'])
          && in_array(strtoupper($filterValues['direction']), array('ASC', 'DESC'))) {
          $dql.= ' ' . strtoupper($filterValues['direction']);
        }
      }
    }

    // create query
    $query = $this->_em->createQuery($dql);
    $query->setParameters($dqlParams);

    // set limits
    if (!$ignoreLimit) {
      if (isset($filterValues['start']) && isset($filterValues['limit'])) {
        if ($filterValues['start'] > 0 && $filterValues['limit'] > 0) {
          $query->setFirstResult($filterValues['start']);
        }
      }
      if (isset($filterValues['limit'])) {
        if ($filterValues['limit'] > 0) {
          $query->setMaxResults($filterValues['limit']);
        }
      }
    }
    
    return $query->getResult();
  }
}
