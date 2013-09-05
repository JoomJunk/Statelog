<?php
/**
 * @package    JJ_Statelog
 *
 * @copyright  Copyright (C) 2011 - 2013 JoomJunk. All rights reserved.
 * @license    GPL v3.0 or later http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

// Import JLog class
jimport('joomla.log.log');

/**
 * Statelog Logging Plugin
 *
 * @package  JJ_Statelog
 * @since    1.0
 */
class PlgContentStatelog extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor - sets up the log file and category.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		// Log mod_shoutbox errors to specific file.
		JLog::addLogger(
			array(
				'text_file' => 'plg_jj_statelog.php',
				'text_entry_format' => '{DATE} {TIME} {CLIENTIP} {TYPE} {USERNAME} {ARTICLE} {COMPONENT} {MESSAGE}',
			),
			JLog::ALL,
			'plg_jj_statelog'
		);
		parent::__construct($subject, $config);
	}

	/**
	 * Logs when a content item's state is changed
	 *
	 * @param   string   $context  The context of the content being passed to the plugin - The component name and view
	 * @param   array    $pks      An array of item Id's that have had their state's changed
	 * @param   integer  $value    The value the state has been changed to
	 *
	 * @return  bool  Returns true at all times to allow the state to be changed
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		if ($this->params->get('log_state', 1))
		{
			if ($value == 1)
			{
				$state = JText::_('JUNPUBLISHED');
			}
			elseif ($value == 0)
			{
				$state = JText::_('JPUBLISHED');
			}
			elseif ($value == -2)
			{
				$state = JText::_('JTRASHED');
			}
			elseif ($value == 2)
			{
				$state = JText::_('JARCHIVED');
			}
			else
			{
				// What have you changed the state to? This is witchcraft! ABORT!!
				return true;
			}

			// Create the log message.
			$user = JFactory::getUser();
			$parts = explode(".", $context);

			foreach ($pks as $pk)
			{
				$logEntry = new JLogEntry('', JLog::INFO, 'plg_jj_statelog');
				$logEntry->type = $state;
				$logEntry->userName = $user->get('name');
				$logEntry->article = $pk;
				$logEntry->component = $parts[0];

				JLog::add($logEntry);
			}
		}

		return true;
	}

	/**
	 * Logs when an article has been deleted
	 *
	 * @param   string         $context  The context of the content being passed to the plugin - The component name and view
	 * @param   JTableContent  $article  A reference to the JTableContent object that has been deleted which holds the article data.
	 *
	 * @return  bool  Returns true at all times to allow the state to be changed
	 */
	public function onContentAfterDelete($context, $article)
	{
		if ($this->params->get('log_delete', 1))
		{
			$user = JFactory::getUser();
			$parts = explode(".", $context);

			// Create the log message.
			$logEntry = new JLogEntry('', JLog::INFO, 'plg_jj_statelog');
			$logEntry->type = JText::_('PLG_JJ_STATELOG_DELETED');
			$logEntry->userName = $user->get('name');
			$logEntry->article = $article->id;
			$logEntry->component = $parts[0];

			// Add the message.
			JLog::add($logEntry);
		}

		return true;
	}
}
