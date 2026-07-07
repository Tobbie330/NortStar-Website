"""Adapter registry — add new integrations here (one line per adapter)."""
from .cameras import GenericCameraIntegration
from .demo import DemoIntegration
from .hubspace import HubspaceIntegration
from .lacrosse_view import LaCrosseIntegration
from .roku import RokuIntegration
from .wyze import WyzeIntegration

ADAPTERS = {
    cls.id: cls
    for cls in (
        DemoIntegration,
        WyzeIntegration,
        RokuIntegration,
        LaCrosseIntegration,
        HubspaceIntegration,
        GenericCameraIntegration,
    )
}
