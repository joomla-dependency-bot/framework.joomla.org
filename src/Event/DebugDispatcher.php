<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Event;

use DebugBar\DebugBar;
use Joomla\Event\{
	DispatcherInterface, EventInterface, SubscriberInterface, SubscriberManagerInterface
};

/**
 * Debug event dispatcher
 */
class DebugDispatcher implements DispatcherInterface, SubscriberManagerInterface
{
	/**
	 * Application debug bar
	 *
	 * @var  DebugBar
	 */
	private $debugBar;

	/**
	 * The delegated dispatcher
	 *
	 * @var  DispatcherInterface
	 */
	private $dispatcher;

	/**
	 * Event subscriber constructor.
	 *
	 * @param   DispatcherInterface  $dispatcher  The delegated dispatcher
	 * @param   DebugBar             $debugBar    Application debug bar
	 */
	public function __construct(DispatcherInterface $dispatcher, DebugBar $debugBar)
	{
		$this->debugBar   = $debugBar;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Attaches a listener to an event
	 *
	 * @param   string    $eventName  The event to listen to.
	 * @param   callable  $callback   A callable function
	 * @param   integer   $priority   The priority at which the $callback executed
	 *
	 * @return  boolean
	 */
	public function addListener(string $eventName, callable $callback, int $priority = 0): bool
	{
		return $this->dispatcher->addListener($eventName, $callback, $priority);
	}

	/**
	 * Adds an event subscriber.
	 *
	 * @param   SubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 */
	public function addSubscriber(SubscriberInterface $subscriber)
	{
		if (!($this->dispatcher instanceof SubscriberManagerInterface))
		{
			throw new \RuntimeException(
				sprintf(
					'The decorated dispatcher does not implement `%s`.',
					SubscriberManagerInterface::class
				)
			);
		}

		$this->dispatcher->addSubscriber($subscriber);
	}

	/**
	 * Dispatches an event to all registered listeners.
	 *
	 * @param   string          $name   The name of the event to dispatch.
	 * @param   EventInterface  $event  The event to pass to the event handlers/listeners.
	 *
	 * @return  EventInterface  The event after being passed through all listeners.
	 */
	public function dispatch(string $name, EventInterface $event = null): EventInterface
	{
		/** @var \DebugBar\DataCollector\TimeDataCollector $collector */
		$collector = $this->debugBar['time'];
		$label     = 'dispatching ' . $name;

		$collector->startMeasure($label);

		$event = $this->dispatcher->dispatch($name, $event);

		// Needed because the application's before respond event may be cut short
		if ($collector->hasStartedMeasure($label))
		{
			$collector->stopMeasure($label);
		}

		return $event;
	}

	/**
	 * Removes an event listener from the specified event.
	 *
	 * @param   string    $eventName  The event to remove a listener from.
	 * @param   callable  $listener   The listener to remove.
	 *
	 * @return  void
	 */
	public function removeListener(string $eventName, callable $listener)
	{
		$this->dispatcher->removeListener($eventName, $listener);
	}

	/**
	 * Removes an event subscriber.
	 *
	 * @param   SubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 */
	public function removeSubscriber(SubscriberInterface $subscriber)
	{
		if (!($this->dispatcher instanceof SubscriberManagerInterface))
		{
			throw new \RuntimeException(
				sprintf(
					'The decorated dispatcher does not implement `%s`.',
					SubscriberManagerInterface::class
				)
			);
		}

		$this->dispatcher->removeSubscriber($subscriber);
	}

	/**
	 * Trigger an event.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  EventInterface  The event after being passed through all listeners.
	 */
	public function triggerEvent($event)
	{
		if (!method_exists($this->dispatcher, 'triggerEvent'))
		{
			throw new \RuntimeException('The decorated dispatcher does not implement the `triggerEvent` method.');
		}

		return $this->dispatcher->triggerEvent($event);
	}
}