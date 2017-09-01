Feature: event proxy
  Proxies all event from zf2 to broadway event bus

  Scenario: an event is proxied to event bus
  When I send a POST request to "/eventproxy" with body:
    """
      {}
    """