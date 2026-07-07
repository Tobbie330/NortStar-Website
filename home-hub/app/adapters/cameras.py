"""Generic camera integration.

Lets you add ANY camera that exposes a browser-playable stream — an ONVIF /
RTSP camera restreamed through go2rtc or mediamtx, an NVR's HLS feed, a
doorbell's MJPEG URL, etc. This is also the escape hatch for brands without
an open API (like Roku Smart Home cameras behind a restreamer).

config.yaml example:
  cameras:
    enabled: true
    cameras:
      - name: Back Porch
        hls_url: http://192.168.1.50:1984/api/stream.m3u8?src=porch
        snapshot_url: http://192.168.1.50:1984/api/frame.jpeg?src=porch
      - name: Garage (MJPEG)
        mjpeg_url: http://192.168.1.61/mjpeg
"""
from __future__ import annotations

from ..models import Device, DeviceType, StreamInfo
from .base import Integration


class GenericCameraIntegration(Integration):
    id = "cameras"
    name = "Cameras (generic)"

    async def get_devices(self) -> list[Device]:
        devices = []
        for cam in self.config.get("cameras", []) or []:
            name = cam.get("name", "Camera")
            devices.append(Device(
                id=cam.get("id") or name.lower().replace(" ", "-"),
                integration=self.id,
                name=name,
                type=DeviceType.CAMERA,
                online=True,
                stream=StreamInfo(
                    hls=cam.get("hls_url"),
                    webrtc=cam.get("webrtc_url"),
                    mjpeg=cam.get("mjpeg_url"),
                    snapshot=cam.get("snapshot_url"),
                    rtsp=cam.get("rtsp_url"),
                ),
            ))
        return devices
